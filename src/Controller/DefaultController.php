<?php

namespace Davamigo\TranslatorBundle\Controller;

use Davamigo\TranslatorBundle\Form\SaveForm;
use Davamigo\TranslatorBundle\Model\Translator\FileCreatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package Davamigo\TranslatorBundle\Controller
 * @author David Amigo <davamigo@gmail.com>
 * @Security("has_role('ROLE_ADMIN')")
 */
class DefaultController extends Controller
{
    /** Translation session key */
    const SESSION_KEY = 'davamigo.translators';

    /**
     * List of all of the resources and translations in the app
     *
     * @return array
     *
     * @Route("/")
     * @Route("", name="translations_index")
     * @Template("@DavamigoTranslator/Default/index.html.twig")
     */
    public function indexAction()
    {
        $storage = $this->get('davamigo.translator.storage.session');
        $scanner = $this->get('davamigo.translator.scanner');

        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }
        else {
            $translations = $scanner->scan()->sort();
            $storage->save($translations, static::SESSION_KEY);
        }

        return array(
            'translations' => $translations
        );
    }

    /**
     * Export the translations to an Excel file
     *
     * @return Response
     *
     * @Route("/export/excel", name="translations_export_excel")
     */
    public function exportExcelAction()
    {
        $translations = null;

        $storage = $this->get('davamigo.translator.storage.session');
        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }

        $excelService = $this->get('davamigo.translator.exporter.excel');
        $response = $excelService->export($translations);

        return $response;
    }

    /**
     * Export the translations to a Yaml file
     *
     * @return Response
     *
     * @Route("/export/yaml", name="translations_export_yaml")
     */
    public function exportYamlAction()
    {
        $translations = null;

        $storage = $this->get('davamigo.translator.storage.session');
        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }

        $yamlService = $this->get('davamigo.translator.exporter.yaml');
        $response = $yamlService->export($translations);

        return $response;
    }

    /**
     * Import translations from an Excel file
     *
     * @param Request $request
     * @return RedirectResponse
     *
     * @Route("/import/excel", name="translations_import_excel")
     */
    public function importExcelAction(Request $request)
    {
        // Objects
        $storage = $this->get('davamigo.translator.storage.session');
        $scanner = $this->get('davamigo.translator.scanner');
        $importer = $this->get('davamigo.translator.importer.excel');
        $flashBag = $this->get('session')->getFlashBag();

        // Get the files from the request
        $files = $request->files->get('files', null);
        $file = $request->files->get('file', null);
        if (null == $files || !is_array($files) || count($files) < 1) {
            if (null != $files && is_object($files)) {
                $files = array($files);
            }
            elseif (null != $file && is_object($file)) {
                $files = array($file);
            }
            else {
                $files = array();
            }
        }

        // Get the current translations
        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }
        else {
            $translations = $scanner->scan()->sort();
        }

        try {
            /** @var UploadedFile[] $files */
            foreach ($files as $file) {
                $translations = $importer->import($file, $translations);
            }

            $message = 'Import result: ';
            $message .= $importer->getReadResources() . ' resources processed. ';
            $message .= $importer->getNewTranslations() . ' new translations inserted.';
            $flashBag->add('info', $message);

            $translations->sort();
            $storage->save($translations, static::SESSION_KEY);
        }
        catch (\Exception $exc) {
            $flashBag->add('danger', $exc->getMessage());
        }

        return $this->redirectToRoute('translations_index');
    }
    /**
     * Save new translations (in YAML format)
     *
     * @param Request $request
     * @return array|RedirectResponse
     *
     * @Route("/save/yaml", name="translations_save_yaml")
     * @Template("@DavamigoTranslator/Default/form.html.twig")
     */
    public function saveYamlAction(Request $request)
    {
        $translations = null;

        $storage = $this->get('davamigo.translator.storage.session');
        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }

        $bundles = $translations->getBundles();
        $domains = $translations->getDomains();
        $locales = $translations->getLocales();

        $formData = array(
            'bundles'   => $bundles,
            'domains'   => $domains,
            'locales'   => $locales,
            'files'     => array()
        );

        $formType = new SaveForm();
        $formTypeName = $formType->getName();
        $formRequest = $request->request->get($formTypeName, array(
            'step'      => 1,
            '_bundles'  => '',
            '_domains'  => '',
            '_locales'  => '',
        ));

        $step = $formRequest['step'];
        switch ($step) {

            case 1:
            default:

                $form = $this->createForm($formType, $formData, array(
                    'step'      => 1,
                    'bundles'   => $bundles,
                    'domains'   => $domains,
                    'locales'   => $locales,
                    'action'    => $this->generateUrl('translations_save_yaml')
                ));

                $form->handleRequest($request);
                if ($form->isValid()) {
                    $formData = $form->getData();

                    $files = $translations->getFiles(
                        $formData['bundles'],
                        $formData['domains'],
                        $formData['locales']
                    );

                    $formData['files'] = array_keys($files);

                    $filesChoices = array_map(
                        function($file) {
                            return $file['folder'] . '/' . $file['filename'];
                        },
                        $files
                    );

                    $form = $this->createForm($formType, $formData, array(
                        'step'      => 2,
                        'files'     => $filesChoices,
                        'action'    => $this->generateUrl('translations_save_yaml')
                    ));
                }
                break;

            case 2:

                $formData['bundles'] = explode('|', $formRequest['_bundles']);
                $formData['domains'] = explode('|', $formRequest['_domains']);
                $formData['locales'] = explode('|', $formRequest['_locales']);

                $files = $translations->getFiles(
                    $formData['bundles'],
                    $formData['domains'],
                    $formData['locales']
                );

                $formData['files'] = array_keys($files);

                $filesChoices = array_map(
                    function($file) {
                        return $file['folder'] . '/' . $file['filename'];
                    },
                    $files
                );

                $form = $this->createForm($formType, $formData, array(
                    'step'      => 2,
                    'files'     => $filesChoices,
                    'action'    => $this->generateUrl('translations_save_yaml')
                ));

                $form->handleRequest($request);
                if ($form->isValid()) {
                    $formData = $form->getData();

                    /** @var FileCreatorInterface $fileCreator */
                    $fileCreator = $this->get('davamigo.translator.file_creator.yaml');

                    $result = array();
                    $data = array_intersect_key($files, array_flip($formData['files']));
                    foreach ($data as $key => $item) {
                        $filename = $item['folder'] . '/' . $item['filename'];
                        $bundle = $item['bundle'];
                        $domain = $item['domain'];
                        $locale = $item['locale'];

                        try {
                            $fileCreator->createFile($translations, $bundle, $domain, $locale, $filename);
                            $result[$key] = array(
                                'result'    => true,
                                'filename'  => $filename
                            );
                        }
                        catch (\Exception $exc) {
                            $result[$key] = array(
                                'result'    => false,
                                'filename'  => $filename,
                                'message'   => $exc->getMessage()
                            );
                        }

                        $form = $this->createForm($formType, $formData, array(
                            'step'      => 3,
                            'result'    => $result,
                            'action'    => $this->generateUrl('translations_save_yaml')
                        ));
                    }

                }
                break;
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Reload all the translations
     *
     * @return RedirectResponse
     *
     * @Route("/reset", name="translations_reset",)
     */
    public function resetAction()
    {
        $storage = $this->get('davamigo.translator.storage.session');
        $scanner = $this->get('davamigo.translator.scanner');

        $translations = $scanner->scan()->sort();
        $storage->save($translations, static::SESSION_KEY);

        return $this->redirectToRoute('translations_index');
    }

    /**
     * List of all of the resources and translations in the app (json format)
     *
     * @return JsonResponse
     *
     * @Route("/results.json", name="translations_result")
     */
    public function resultsAction()
    {
        $translations = null;
        $data = array();

        $storage = $this->get('davamigo.translator.storage.session');
        if ($storage->hasValid(static::SESSION_KEY)) {
            $translations = $storage->load(static::SESSION_KEY);
        }

        if (null !== $translations) {
            $data = $translations->asArray();
        }

        return new JsonResponse(array(
            'data' => $data
        ));
    }
}
