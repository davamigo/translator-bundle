<?php

namespace Davamigo\TranslatorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form to select the data to save
 *
 * @package Davamigo\TranslatorBundle\Form
 * @author davamigo@gmail.com
 */
class SaveForm extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'davamigo_translator_save_form';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'action'                => null,
            'bundles'               => array(),
            'domains'               => array(),
            'locales'               => array(),
            'files'                 => array(),
            'result'                => array(),
            'translation_domain'    => 'DavamigoTranslatorBundle'
        ));

        $resolver->setRequired(array(
            'action',
            'step'
        ));

        $resolver->setAllowedTypes(array(
            'action'    => 'string',
            'step'      => 'int',
            'bundles'   => 'array',
            'domains'   => 'array',
            'locales'   => 'array',
            'files'     => 'array',
            'result'    => 'array'
        ));

        $resolver->setAllowedValues(array(
            'step'      => array(1, 2, 3)
        ));
    }

    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            array($this, 'onPreSetData')
        );
    }

    /**
     * Sets the dynamic data of the form
     *
     * @param FormEvent $event
     * @return $this
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var FormInterface $form */
        $form = $event->getForm();

        /** @var FormConfigInterface $config */
        $config = $form->getConfig();

        /** @var array $options */
        $options = $config->getOptions();
        $step = $options['step'];

        /** @var array $data */
        $data = $event->getData();

        $form->add(
            'step',
            'hidden',
            array(
                'mapped'    => false,
                'data'      => $step
            )
        );

        switch($step) {

            case 1:
                $this->buildFormWhenStep1($form, $data, $options);
                break;

            case 2:
                $this->buildFormWhenStep2($form, $data, $options);
                break;

            case 3:
                $this->buildFormWhenStep3($form, $data, $options);
                break;
        }
    }

    /**
     * Build form when the step==1
     *
     * @param FormInterface $form
     * @param array         $data
     * @param array         $options
     */
    protected function buildFormWhenStep1(FormInterface $form, array $data, array $options)
    {
        $bundles = $options['bundles'];
        $domains = $options['domains'];
        $locales = $options['locales'];

        $form->add(
            '_label',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => 'str.form.save.step1.label'
            )
        );

        $form->add(
            'bundles',
            'choice',
            array(
                'label'                 => 'str.label.bundles',
                'expanded'              => true,
                'multiple'              => true,
                'choices'               => array_combine($bundles, $bundles),
                'constraints'           => new NotBlank(array(
                    'message'               => 'str.form.save.step1.error.bundles'
                ))
            )
        );

        $form->add(
            'domains',
            'choice',
            array(
                'label'                 => 'str.label.domains',
                'expanded'              => true,
                'multiple'              => true,
                'choices'               => array_combine($domains, $domains),
                'constraints'           => new NotBlank(array(
                    'message'               => 'str.form.save.step1.error.domains'
                ))
            )
        );

        $form->add(
            'locales',
            'choice',
            array(
                'label'                 => 'str.label.locales',
                'expanded'              => true,
                'multiple'              => true,
                'choices'               => array_combine($locales, $locales),
                'constraints'           => new NotBlank(array(
                    'message'               => 'str.form.save.step1.error.locales'
                ))
            )
        );

        $form->add(
            'submit',
            'submit',
            array(
                'label'                 => 'str.action.save'
            )
        );
    }

    /**
     * Build form when the step==2
     *
     * @param FormInterface $form
     * @param array         $data
     * @param array         $options
     */
    protected function buildFormWhenStep2(FormInterface $form, array $data, array $options)
    {
        $files = $options['files'];

        $form->add(
            '_label',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => 'str.form.save.step2.label'
            )
        );

        $form->add(
            '_info',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => 'str.form.save.step2.info'
            )
        );

        $form->add(
            '_bundles',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => implode('|', $data['bundles'])
            )
        );

        $form->add(
            '_domains',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => implode('|', $data['domains'])
            )
        );

        $form->add(
            '_locales',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => implode('|', $data['locales'])
            )
        );

        $form->add(
            'files',
            'choice',
            array(
                'label'                 => 'str.label.files',
                'expanded'              => true,
                'multiple'              => true,
                'choices'               => $files,
                'constraints'           => new NotBlank(array(
                    'message'               => 'str.form.save.step2.error.files'
                ))
            )
        );

        $form->add(
            'submit',
            'submit',
            array(
                'label'                 => 'str.action.save'
            )
        );
    }

    /**
     * Build form when the step==3
     *
     * @param FormInterface $form
     * @param array         $data
     * @param array         $options
     */
    protected function buildFormWhenStep3(FormInterface $form, array $data, array $options)
    {
        $result = $options['result'];

        $form->add(
            '_label',
            'hidden',
            array(
                'mapped'                => false,
                'data'                  => 'str.form.save.step3.label'
            )
        );

        $success = array();
        $errors = array();

        foreach ($result as $item) {
            if ($item['result']) {
                $success[] = $item['filename'];
            } else {
                $errors[] = $item['message'];
            }
        }

        if (empty($success) && empty($errors)) {
            $form->add(
                '_error',
                'hidden',
                array(
                    'mapped'            => false,
                    'data'              => 'str.form.save.step3.error'
                )
            );
        } else {
            if (!empty($success)) {
                $form->add(
                    '_success',
                    'hidden',
                    array(
                        'mapped'            => false,
                        'data'              => 'str.form.save.step3.success',
                    )
                );
            }

            if (!empty($errors)) {
                $form->add(
                    '_errors',
                    'hidden',
                    array(
                        'mapped'            => false,
                        'data'              => implode('|', $errors)
                    )
                );
            }
        }
    }
}
