<?php

namespace Ifraktal\TranslatorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Functional test of the default controller (in the IfraktalTranslatorBundle)
 *
 * @package Ifraktal\TranslatorBundle\Tests\Controller
 * @author David Amigo <davamigo@gmail.com>
 */
class DefaultControllerTest extends WebTestCase
{
    /** @var Client */
    private $client = null;

    /** @var ContainerInterface */
    private $container = null;

    /** @var Router */
    private $router;

    /**
     * Set up functional tests
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->router = $this->container->get('router');
    }

    /**
     * Functional test of index action
     */
    public function testIndexAction()
    {
        $this->logIn();

        $route = $this->router->generate('translations_index');
        $crawler = $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $this->assertEquals(1, $crawler->filter('html:contains("Symfony Translations - Ifraktal")')->count());
        $this->assertEquals(1, $crawler->filter('a.js-btn-download')->count());
        $this->assertEquals(1, $crawler->filter('a.js-btn-upload')->count());
        $this->assertEquals(1, $crawler->filter('a.js-btn-save')->count());
        $this->assertEquals(1, $crawler->filter('a.js-btn-reset')->count());
        $this->assertEquals(1, $crawler->filter('a.js-btn-locale-add')->count());

        $form = $crawler->filter('form.js-files-form');
        $this->assertEquals(1, $form->count());
        $this->assertEquals(1, $form->filter('input.js-files-input')->count());

        $table = $crawler->filter('table.js-table');
        $this->assertEquals(1, $table->count());
        $this->assertEquals(1, $table->filter('td.js-column-bundle')->count());
        $this->assertEquals(1, $table->filter('td.js-column-domain')->count());
        $this->assertEquals(1, $table->filter('td.js-column-resource')->count());
        $this->assertEquals(1, $table->filter('th[data-column-id="bundle"]')->count());
        $this->assertEquals(1, $table->filter('th[data-column-id="domain"]')->count());
        $this->assertEquals(1, $table->filter('th[data-column-id="resource"]')->count());
    }

    /**
     * Functional test of export excel action
     */
    public function testExportExcelAction()
    {
        $this->logIn();
        $this->setupSessionData();

        /**
         * This action returns an StreamedResponse (a Response that uses a callback fot its content). Using on_start()
         * and ob_get_clean() is necessary to avoid reading problems in the test
         */

        ob_start();
        $route = $this->router->generate('translations_export_excel');
        $this->client->request('GET', $route);
        $excelContent = ob_get_clean();
        $this->assertNotNull($excelContent);

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $this->assertContains('vnd.ms-excel', $response->headers->get('Content-Type'));
        $this->assertContains('ifraktal_translator_', $response->headers->get('Content-Disposition'));
    }

    /**
     * Functional test of export yaml action
     */
    public function testExportYamlAction()
    {
        $this->logIn();
        $this->setupSessionData();

        $route = $this->router->generate('translations_export_yaml');
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * Functional test of import excel action
     */
    public function testImportExcelAction()
    {
        $this->logIn();
        $this->setupSessionData();

        // Generate an Excel content
        ob_start();
        $route = $this->router->generate('translations_export_excel');
        $this->client->request('GET', $route);
        $excelContent = ob_get_clean();
        $this->assertNotNull($excelContent);

        // Save the excel content in a temp file
        $filename = tempnam(sys_get_temp_dir(), 'ifk_');
        $bytesCount = file_put_contents($filename, $excelContent);
        $this->assertGreaterThan(0, $bytesCount);
        $file = new UploadedFile($filename, 'test.xls');

        // Call import excel action
        $route = $this->router->generate('translations_import_excel');
        $this->client->request('GET', $route, array(), array( $file ));
        $response = $this->client->getResponse();
        $this->assertTrue($response->isRedirect($this->router->generate('translations_index')));

        // Delete the temp file
        unlink($filename);
    }

    /**
     * Functional test of reset action
     */
    public function testResetAction()
    {
        $this->logIn();

        $route = $this->router->generate('translations_reset');
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertTrue($response->isRedirect($this->router->generate('translations_index')));
    }

    /**
     * Functional test of results action
     */
    public function testResultsAction()
    {
        $this->logIn();
        $this->setupSessionData();

        $route = $this->router->generate('translations_result');
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertContains('json', $response->headers->get('Content-Type'));

        $rawContent = $response->getContent();
        $content = json_decode($rawContent, true);
        $this->assertTrue(isset($content['data']));
    }

    /**
     * Simulates the log-in process
     *
     * @return $this
     */
    protected function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'default';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $this;
    }

    /**
     * Many actions need the translations saved in session
     *
     * @return $this
     */
    protected function setupSessionData()
    {
        $scanner = $this->container->get('ifraktal.translator.scanner');
        $translations = $scanner->scan()->sort();
        $this->assertNotNull($translations);

        $storage = $this->container->get('ifraktal.translator.storage.session');
        $result = $storage->save($translations);
        $this->assertTrue($result);

        return $this;
    }
}
