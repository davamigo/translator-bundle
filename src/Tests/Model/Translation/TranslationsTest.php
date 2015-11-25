<?php

namespace Davamigo\TranslatorBundle\Tests\Model\Translation;

use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Davamigo\TranslatorBundle\Tests\BaseTestCase;

/**
 * Unit test of the Translations class
 *
 * @package Davamigo\TranslatorBundle\Tests\Model\Translation
 * @author David Amigo <davamigo@gmail.com>
 */
class TranslationsTest extends BaseTestCase
{
    /**
     * Basic test of add translations.
     */
    public function testAddTranslationsWorksProperly()
    {
        // Test data
        $bundle = 'bundle#1';
        $domain = 'domain#1';
        $locale = 'locales#1';
        $resource = 'resource#1';
        $translation = 'translation#1';

        // Run the test
        $translations = new Translations();
        $translations->addTranslation($bundle, $domain, $locale, $resource, $translation);

        // Expected result
        $bundles = array(
            $bundle
        );

        $domains = array(
            $domain
        );

        $locales = array(
            $locale
        );

        $files = array();

        $messages = array(
            $bundle => array(
                $domain => array(
                    $locale => array(
                        $resource => $translation
                    )
                )
            )
        );

        // Assertions
        $this->assertEquals($bundles, $this->getPrivateValue($translations, 'bundles'));
        $this->assertEquals($domains, $this->getPrivateValue($translations, 'domains'));
        $this->assertEquals($locales, $this->getPrivateValue($translations, 'locales'));
        $this->assertEquals($files, $this->getPrivateValue($translations, 'files'));
        $this->assertEquals($messages, $this->getPrivateValue($translations, 'messages'));
    }

    /**
     * Basic test of add translations.
     */
    public function testAddTranslationsWithNullParamsThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->addTranslation(null, null, null, null, null);
    }

    /**
     * Basic test of add translations from a Symfony catalogue.
     */
    public function testAddCatalogueWorksAsExpected()
    {
        // Test data
        $bundle = 'bundle#1';
        $domain = 'domain#1';
        $locale = 'locales#1';
        $resource = 'resource#1';
        $translation = 'translation#1';
        $path = '/some/path';
        $filename = 'file.ext';

        // Mocks
        $catalogueMock = $this
            ->getMockBuilder('Symfony\Component\Translation\MessageCatalogueInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resourceMock = $this
            ->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $catalogueMock
            ->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $catalogueMock
            ->expects($this->once())
            ->method('getDomains')
            ->will($this->returnValue(array($domain)));

        $catalogueMock
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array($resource => $translation)));

        $catalogueMock
            ->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array($resourceMock)));

        $resourceMock
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($filename));

        // Run the test
        $translations = new Translations();
        $translations->addCatalogue($bundle, $path, $catalogueMock);

        // Expected result
        $bundles = array(
            $bundle
        );

        $domains = array(
            $domain
        );

        $locales = array(
            $locale
        );

        $files = array(
            $bundle => array(
                $path => array(
                    $filename
                )
            )
        );

        $messages = array(
            $bundle => array(
                $domain => array(
                    $locale => array(
                        $resource => $translation
                    )
                )
            )
        );

        // Assertions
        $this->assertEquals($bundles, $this->getPrivateValue($translations, 'bundles'));
        $this->assertEquals($domains, $this->getPrivateValue($translations, 'domains'));
        $this->assertEquals($locales, $this->getPrivateValue($translations, 'locales'));
        $this->assertEquals($files, $this->getPrivateValue($translations, 'files'));
        $this->assertEquals($messages, $this->getPrivateValue($translations, 'messages'));
    }

    /**
     * Test of merge messages of two translation objects
     */
    public function testMergeMessagesWorksAsExpected()
    {
        // Test data
        $messages1 = array(
            'bundle#1' => array(
                'domain#1' => array(
                    'locale#1' => array(
                        'resource#1' => 'translation#1',
                        'resource#2' => 'translation#2'
                    ),
                    'locale#2' => array(
                        'resource#3' => 'translation#3'
                    )
                ),
                'domain#2' => array(
                    'locale#1' => array(
                        'resource#4' => 'translation#4'
                    )
                )
            ),
            'bundle#2' => array(
                'domain#1' => array(
                    'locale#2' => array(
                        'resource#5' => 'translation#5'
                    )
                )
            )
        );

        $messages2 = array(
            'bundle#2' => array(
                'domain#1' => array(
                    'locale#1' => array(
                        'resource#6' => 'translation#6',
                    )
                )
            ),
            'bundle#3' => array(
                'domain#3' => array(
                    'locale#3' => array(
                        'resource#7' => 'translation#7'
                    )
                )
            )
        );

        // Run the test
        $translations1 = new Translations();
        $this->setPrivateValue($translations1, 'messages', $messages1);

        $translations2 = new Translations();
        $this->setPrivateValue($translations2, 'messages', $messages2);

        $result = $translations1->merge($translations2);

        // Expected result
        $messages = array(
            'bundle#1' => array(
                'domain#1' => array(
                    'locale#1' => array(
                        'resource#1' => 'translation#1',
                        'resource#2' => 'translation#2'
                    ),
                    'locale#2' => array(
                        'resource#3' => 'translation#3'
                    )
                ),
                'domain#2' => array(
                    'locale#1' => array(
                        'resource#4' => 'translation#4'
                    )
                )
            ),
            'bundle#2' => array(
                'domain#1' => array(
                    'locale#2' => array(
                        'resource#5' => 'translation#5'
                    ),
                    'locale#1' => array(
                        'resource#6' => 'translation#6',
                    )
                )
            ),
            'bundle#3' => array(
                'domain#3' => array(
                    'locale#3' => array(
                        'resource#7' => 'translation#7'
                    )
                )
            )
        );

        // Assertions
        $this->assertEquals($messages, $this->getPrivateValue($result, 'messages'));
    }

    /**
     * Test of merge files of two translation objects
     */
    public function testMergeFilesWorksAsExpected()
    {
        // Test data
        $files1 = array(
            'bundle#1' => array(
                'path#1' => array(
                    'filename#1',
                    'filename#2'
                ),
                'path#2' => array(
                    'filename#1'
                )
            ),
            'bundle#2' => array(
                'path#1' => array(
                    'filename#2'
                )
            )
        );

        $files2 = array(
            'bundle#2' => array(
                'path#1' => array(
                    'filename#1'
                )
            ),
            'bundle#3' => array(
                'path#3' => array(
                    'filename#3'
                )
            )
        );

        // Run the test
        $translations1 = new Translations();
        $this->setPrivateValue($translations1, 'files', $files1);

        $translations2 = new Translations();
        $this->setPrivateValue($translations2, 'files', $files2);

        $result = $translations1->merge($translations2);

        // Expected result
        $files = array(
            'bundle#1' => array(
                'path#1' => array(
                    'filename#1',
                    'filename#2'
                ),
                'path#2' => array(
                    'filename#1'
                )
            ),
            'bundle#2' => array(
                'path#1' => array(
                    'filename#2',
                    'filename#1'
                )
            ),
            'bundle#3' => array(
                'path#3' => array(
                    'filename#3'
                )
            )
        );

        // Assertions
        $this->assertEquals($files, $this->getPrivateValue($result, 'files'));
    }

    /**
     * Basic test of sort translations
     */
    public function testSortWorksAsExpected()
    {
        // Test data
        $source = array(
            array('bundle#3', 'domain#2', 'locale#1', 'resource#4', 'translation#4'),
            array('bundle#1', 'domain#1', 'locale#2', 'resource#1', 'translation#1'),
            array('bundle#2', 'domain#1', 'locale#1', 'resource#5', 'translation#5'),
            array('bundle#1', 'domain#2', 'locale#1', 'resource#3', 'translation#3'),
            array('bundle#2', 'domain#1', 'locale#1', 'resource#2', 'translation#2'),
        );

        // Run the test
        $translations = new Translations();
        foreach ($source as $item) {
            $translations->addTranslation($item[0], $item[1], $item[2], $item[3], $item[4]);
        }
        $translations->sort();

        // Expected result
        $bundles = array(
            'bundle#1',
            'bundle#2',
            'bundle#3'
        );

        $domains = array(
            'domain#1',
            'domain#2'
        );

        $locales = array(
            'locale#1',
            'locale#2'
        );

        $files = array();

        $messages = array(
            'bundle#1' => array(
                'domain#1' => array(
                    'locale#2' => array(
                        'resource#1' => 'translation#1'
                    )
                ),
                'domain#2' => array(
                    'locale#1' => array(
                        'resource#3' => 'translation#3'
                    )
                )
            ),
            'bundle#2' => array(
                'domain#1' => array(
                    'locale#1' => array(
                        'resource#2' => 'translation#2',
                        'resource#5' => 'translation#5'
                    )
                )
            ),
            'bundle#3' => array(
                'domain#2' => array(
                    'locale#1' => array(
                        'resource#4' => 'translation#4'
                    )
                )
            )
        );

        // Assertions
        $this->assertEquals($bundles, $this->getPrivateValue($translations, 'bundles'));
        $this->assertEquals($domains, $this->getPrivateValue($translations, 'domains'));
        $this->assertEquals($locales, $this->getPrivateValue($translations, 'locales'));
        $this->assertEquals($files, $this->getPrivateValue($translations, 'files'));
        $this->assertEquals($messages, $this->getPrivateValue($translations, 'messages'));
    }

    /**
     * Basic test of get bundles
     */
    public function testGetBundlesReturnsAllBundles()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundle#1',
            'bundle#2'
        );

        // Assertions
        $result = $translations->getBundles();
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get domains
     */
    public function testGetDomainsWithoutParamReturnsAllDomains()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'domain#1',
            'domain#2',
            'domain#3'
        );

        // Assertions
        $result = $translations->getDomains();
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get domains
     */
    public function testGetDomainsWithValidParamsReturnsTheDomainsFiltered()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'domain#1',
            'domain#2'
        );

        // Assertions
        $result = $translations->getDomains('bundle#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get domains
     */
    public function testGetDomainsWithNoValidParamsReturnsAnEmptyArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array();

        // Assertions
        $result = $translations->getDomains('bundle#3');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get locales
     */
    public function testGetLocalesWithoutParamReturnsAllLocales()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'locale#1',
            'locale#2',
            'locale#3'
        );

        // Assertions
        $result = $translations->getLocales();
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get locales
     */
    public function testGetLocalesWithValidParamsReturnsTheLocalesFiltered()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'locale#1',
            'locale#2'
        );

        // Assertions
        $result = $translations->getLocales('bundle#1', 'domain#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get locales
     */
    public function testGetLocalesWithNoValidParamsReturnsAnEmptyArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array();

        // Assertions
        $result = $translations->getLocales('bundle#3', 'domain#4');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get resources
     */
    public function testGetResourcesWithValidBundleAndDomainReturnsTheResourcesFiltered()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'resource#1',
            'resource#2'
        );

        // Assertions
        $result = $translations->getResources('bundle#1', 'domain#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get resources
     */
    public function testGetResourcesWithValidBundleDomainAndLocaleReturnsTheResourcesFiltered()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'resource#3'
        );

        // Assertions
        $result = $translations->getResources('bundle#2', 'domain#3', 'locale#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get resources
     */
    public function testGetResourcesWithNullParamsThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->getResources(null, null);
    }

    /**
     * Basic test of get resources
     */
    public function testGetResourcesWithoutValidBundleAndDomainReturnsAnEmptyArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array();

        // Assertions
        $result = $translations->getResources('bundle#9', 'domain#9');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get resources
     */
    public function testGetResourcesWithoutValidLocaleReturnsAnEmptyArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array();

        // Assertions
        $result = $translations->getResources('bundle#1', 'domain#1', 'domain#9');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get messages
     */
    public function testGetMessagesWithValidParamsReturnsTheMessagesFiltered()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'resource#1' => 'translation#01',
            'resource#2' => 'translation#02'
        );

        // Assertions
        $result = $translations->getMessages('bundle#1', 'domain#1', 'locale#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get messages
     */
    public function testGetMessagesWithoutValidParamsReturnsAnEmptyArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array();

        // Assertions
        $result = $translations->getMessages('bundle#9', 'domain#9', 'locale#9');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get messages
     */
    public function testGetMessagesWithNullParamsThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->getMessages(null, null, null);
    }

    /**
     * Basic test of get translation
     */
    public function testGetTranslationWithValidParamsReturnsAnString()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = 'translation#01';

        // Assertions
        $result = $translations->getTranslation('bundle#1', 'domain#1', 'locale#1', 'resource#1');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get translation
     */
    public function testGetTranslationWithoutValidParamsReturnsNull()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = null;

        // Assertions
        $result = $translations->getTranslation('bundle#9', 'domain#9', 'locale#9', 'resource#9');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get translation
     */
    public function testGetTranslationWithNullParamsThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->getTranslation(null, null, null, null);
    }

    /**
     * Basic test of get array of translation files
     */
    public function testGetFilesWithoutParametersReturnsAllFiles()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundle#1|domain#1|locale#1' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#1',
                'locale'    => 'locale#1',
                'folder'    => 'path#2',
                'filename'  => 'domain#1.locale#1.yml',
                'messages'  => 2
            ),
            'bundle#1|domain#1|locale#2' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#1',
                'locale'    => 'locale#2',
                'folder'    => 'path#2',
                'filename'  => 'domain#1.locale#2.yml',
                'messages'  => '2',
            ),
            'bundle#1|domain#2|locale#1' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#2',
                'locale'    => 'locale#1',
                'folder'    => 'path#1',
                'filename'  => 'domain#2.locale#1.yml',
                'messages'  => '2',
            ),
            'bundle#1|domain#2|locale#2' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#2',
                'locale'    => 'locale#2',
                'folder'    => 'path#1',
                'filename'  => 'domain#2.locale#2.yml',
                'messages'  => '1',
            ),
            'bundle#2|domain#1|locale#1' => array(
                'bundle'    => 'bundle#2',
                'domain'    => 'domain#1',
                'locale'    => 'locale#1',
                'folder'    => 'path#4',
                'filename'  => 'domain#1.locale#1.yml',
                'messages'  => '1',
            ),
            'bundle#2|domain#1|locale#2' => array(
                'bundle'    => 'bundle#2',
                'domain'    => 'domain#1',
                'locale'    => 'locale#2',
                'folder'    => 'path#4',
                'filename'  => 'domain#1.locale#2.yml',
                'messages'  => '1',
            ),
            'bundle#2|domain#3|locale#1' => array(
                'bundle'    => 'bundle#2',
                'domain'    => 'domain#3',
                'locale'    => 'locale#1',
                'folder'    => 'path#4',
                'filename'  => 'domain#3.locale#1.yml',
                'messages'  => '1',
            ),
            'bundle#2|domain#3|locale#2' => array(
                'bundle'    => 'bundle#2',
                'domain'    => 'domain#3',
                'locale'    => 'locale#2',
                'folder'    => 'path#4',
                'filename'  => 'domain#3.locale#2.yml',
                'messages'  => '1',
            ),
            'bundle#2|domain#3|locale#3' => array(
                'bundle'    => 'bundle#2',
                'domain'    => 'domain#3',
                'locale'    => 'locale#3',
                'folder'    => 'path#4',
                'filename'  => 'domain#3.locale#3.yml',
                'messages'  => '1',
            )
        );

        // Assertions
        $result = $translations->getFiles();
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get array of translation files
     */
    public function testGetFilesWithParametersReturnsFilteredFiles()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundle#1|domain#1|locale#1' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#1',
                'locale'    => 'locale#1',
                'folder'    => 'path#2',
                'filename'  => 'domain#1.locale#1.yml',
                'messages'  => 2
            ),
            'bundle#1|domain#1|locale#2' => array(
                'bundle'    => 'bundle#1',
                'domain'    => 'domain#1',
                'locale'    => 'locale#2',
                'folder'    => 'path#2',
                'filename'  => 'domain#1.locale#2.yml',
                'messages'  => '2',
            )
        );

        // Assertions
        $result = $translations->getFiles(array('bundle#1'), array('domain#1'), array('locale#1', 'locale#2'));
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of conversion to array
     */
    public function testAsArrayWithoutAssocReturnsSimpleArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            array( 'bundle#1', 'domain#1', 'resource#1', 'translation#01', 'translation#03', null ),
            array( 'bundle#1', 'domain#1', 'resource#2', 'translation#02', 'translation#04', null ),
            array( 'bundle#1', 'domain#2', 'resource#1', 'translation#05', null,             null ),
            array( 'bundle#1', 'domain#2', 'resource#2', null,             'translation#06', null ),
            array( 'bundle#1', 'domain#2', 'resource#3', 'translation#07', null,             null ),
            array( 'bundle#2', 'domain#1', 'resource#1', 'translation#08', null,             null ),
            array( 'bundle#2', 'domain#1', 'resource#2', null,             'translation#09', null ),
            array( 'bundle#2', 'domain#3', 'resource#3', 'translation#10', 'translation#11', 'translation#12' )
        );

        // Assertions
        $result = $translations->asArray(false);
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of conversion to array
     */
    public function testAsArrayWithAssocReturnsAssocArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            array(
                'bundle'        => 'bundle#1',
                'domain'        => 'domain#1',
                'resource'      => 'resource#1',
                'translations'  => array(
                    'locale#1'      => 'translation#01',
                    'locale#2'      => 'translation#03',
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#1',
                'domain'        => 'domain#1',
                'resource'      => 'resource#2',
                'translations'  => array(
                    'locale#1'      => 'translation#02',
                    'locale#2'      => 'translation#04',
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#1',
                'domain'        => 'domain#2',
                'resource'      => 'resource#1',
                'translations'  => array(
                    'locale#1'      => 'translation#05',
                    'locale#2'      => null,
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#1',
                'domain'        => 'domain#2',
                'resource'      => 'resource#2',
                'translations'  => array(
                    'locale#1'      => null,
                    'locale#2'      => 'translation#06',
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#1',
                'domain'        => 'domain#2',
                'resource'      => 'resource#3',
                'translations'  => array(
                    'locale#1'      => 'translation#07',
                    'locale#2'      => null,
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#2',
                'domain'        => 'domain#1',
                'resource'      => 'resource#1',
                'translations'  => array(
                    'locale#1'      => 'translation#08',
                    'locale#2'      => null,
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#2',
                'domain'        => 'domain#1',
                'resource'      => 'resource#2',
                'translations'  => array(
                    'locale#1'      => null,
                    'locale#2'      => 'translation#09',
                    'locale#3'      => null
                ),
            ),
            array(
                'bundle'        => 'bundle#2',
                'domain'        => 'domain#3',
                'resource'      => 'resource#3',
                'translations'  => array(
                    'locale#1'      => 'translation#10',
                    'locale#2'      => 'translation#11',
                    'locale#3'      => 'translation#12'
                )
            )
        );

        // Assertions
        $result = $translations->asArray(true);
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of get raw data
     */
    public function testGetRawDataReturnsAnArray()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundles'   => array(
                'bundle#1',
                'bundle#2'
            ),
            'domains'   => array(
                'domain#1',
                'domain#2',
                'domain#3'
            ),
            'locales'   => array(
                'locale#1',
                'locale#2',
                'locale#3'
            ),
            'files'     => array(
                'bundle#1' => array(
                    'path#1' => array(
                        'domain#1.fileName#1',
                        'domain#2.fileName#2'
                    ),
                    'path#2' => array(
                        'domain#1.fileName#3',
                        'domain#3.fileName#4'
                    )
                ),
                'bundle#2' => array(
                    'path#3' => array(
                        'domain#1.fileName#5',
                        'domain#2.fileName#6'
                    ),
                    'path#4' => array(
                        'domain#1.fileName#7',
                        'domain#3.fileName#8'
                    )
                )
            ),
            'messages'  => array(
                'bundle#1' => array(
                    'domain#1' => array(
                        'locale#1' => array(
                            'resource#1' => 'translation#01',
                            'resource#2' => 'translation#02'
                        ),
                        'locale#2' => array(
                            'resource#1' => 'translation#03',
                            'resource#2' => 'translation#04'
                        )
                    ),
                    'domain#2' => array(
                        'locale#1' => array(
                            'resource#1' => 'translation#05',
                            'resource#3' => 'translation#07'
                        ),
                        'locale#2' => array(
                            'resource#2' => 'translation#06'
                        )
                    )
                ),
                'bundle#2' => array(
                    'domain#1' => array(
                        'locale#1' => array(
                            'resource#1' => 'translation#08'
                        ),
                        'locale#2' => array(
                            'resource#2' => 'translation#09'
                        )
                    ),
                    'domain#3' => array(
                        'locale#1' => array(
                            'resource#3' => 'translation#10'
                        ),
                        'locale#2' => array(
                            'resource#3' => 'translation#11'
                        ),
                        'locale#3' => array(
                            'resource#3' => 'translation#12'
                        )
                    )
                )
            )
        );

        // Assertions
        $result = $translations->getRawData();
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithoutBundlesThrowsAnException()
    {
        // Test data
        $data = array();

        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithoutDomainsThrowsAnException()
    {
        // Test data
        $data = array(
            'bundles' => array()
        );

        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithoutLocalesThrowsAnException()
    {
        // Test data
        $data = array(
            'bundles' => array(),
            'domains' => array()
        );

        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithoutFilesThrowsAnException()
    {
        // Test data
        $data = array(
            'bundles' => array(),
            'domains' => array(),
            'locales' => array()
        );

        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithoutMessagesThrowsAnException()
    {
        // Test data
        $data = array(
            'bundles' => array(),
            'domains' => array(),
            'locales' => array(),
            'files'   => array()
        );

        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);
    }

    /**
     * Basic test of set raw data
     */
    public function testSetRawDataWithValidDataEndsProperly()
    {
        // Test data
        $data = array(
            'bundles'  => array(),
            'domains'  => array(),
            'locales'  => array(),
            'files'    => array(),
            'messages' => array()
        );

        // Run the test
        $translations = new Translations();
        $translations->setRawData($data);

        // Assertions
        $result = $translations->getRawData();
        $this->assertEquals($data, $result);
    }

    /**
     * Basic test of add bundle
     */
    public function testAddNewBundleWorksProperly()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundle#1',
            'bundle#2',
            'bundle#3'
        );

        // Run the test
        $translations->addBundle('bundle#3');

        // Assertions
        $result = $this->getPrivateValue($translations, 'bundles');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add bundle
     */
    public function testAddExistingBundleDoesNothing()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'bundle#1',
            'bundle#2'
        );

        // Run the test
        $translations->addBundle('bundle#1');

        // Assertions
        $result = $this->getPrivateValue($translations, 'bundles');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add domain
     */
    public function testAddNewDomainWorksProperly()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'domain#1',
            'domain#2',
            'domain#3',
            'domain#4'
        );

        // Run the test
        $translations->addDomain('domain#4');

        // Assertions
        $result = $this->getPrivateValue($translations, 'domains');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add domain
     */
    public function testAddExistingDomainWorksDoesNothing()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'domain#1',
            'domain#2',
            'domain#3'
        );

        // Run the test
        $translations->addDomain('domain#1');

        // Assertions
        $result = $this->getPrivateValue($translations, 'domains');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add locale
     */
    public function testAddNewLocaleWorksProperly()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'locale#1',
            'locale#2',
            'locale#3',
            'locale#4'
        );

        // Run the test
        $translations->addLocale('locale#4');

        // Assertions
        $result = $this->getPrivateValue($translations, 'locales');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add locale
     */
    public function testAddExistingLocaleDoesNothing()
    {
        // Test data
        $translations = $this->getConfiguredTranslationsTestObject();

        // Expected result
        $expected = array(
            'locale#1',
            'locale#2',
            'locale#3'
        );

        // Run the test
        $translations->addLocale('locale#1');

        // Assertions
        $result = $this->getPrivateValue($translations, 'locales');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add file
     */
    public function testAddNewFileWorksProperly()
    {
        // Expected result
        $expected = array(
            'bundle#1' => array(
                'path#1' => array(
                    'filename#1'
                )
            )
        );

        // Run the test
        $translations = new Translations();
        $translations->addFile('bundle#1', 'path#1', 'filename#1');


        // Assertions
        $result = $this->getPrivateValue($translations, 'files');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add file
     */
    public function testAddExistingFileDoesNothing()
    {
        // Expected result
        $expected = array(
            'bundle#1' => array(
                'path#1' => array(
                    'filename#1'
                )
            )
        );

        // Run the test
        $translations = new Translations();
        $translations->addFile('bundle#1', 'path#1', 'filename#1');
        $translations->addFile('bundle#1', 'path#1', 'filename#1');


        // Assertions
        $result = $this->getPrivateValue($translations, 'files');
        $this->assertEquals($expected, $result);
    }

    /**
     * Basic test of add file
     */
    public function testAddFileWithoutParamsThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $translations = new Translations();
        $translations->addFile(null, null, null);
    }

    /**
     * Get the source data for many testings
     *
     * @return array
     */
    protected function getTranslationsSourceTestData()
    {
        return array(
            array('bundle#1', 'domain#1', 'locale#1', 'resource#1', 'translation#01'),
            array('bundle#1', 'domain#1', 'locale#1', 'resource#2', 'translation#02'),
            array('bundle#1', 'domain#1', 'locale#2', 'resource#1', 'translation#03'),
            array('bundle#1', 'domain#1', 'locale#2', 'resource#2', 'translation#04'),
            array('bundle#1', 'domain#2', 'locale#1', 'resource#1', 'translation#05'),
            array('bundle#1', 'domain#2', 'locale#2', 'resource#2', 'translation#06'),
            array('bundle#1', 'domain#2', 'locale#1', 'resource#3', 'translation#07'),
            array('bundle#2', 'domain#1', 'locale#1', 'resource#1', 'translation#08'),
            array('bundle#2', 'domain#1', 'locale#2', 'resource#2', 'translation#09'),
            array('bundle#2', 'domain#3', 'locale#1', 'resource#3', 'translation#10'),
            array('bundle#2', 'domain#3', 'locale#2', 'resource#3', 'translation#11'),
            array('bundle#2', 'domain#3', 'locale#3', 'resource#3', 'translation#12'),
        );
    }

    /**
     * Get the source data for many testings
     *
     * @return array
     */
    protected function getFilesSourceTestData()
    {
        return array(
            array('bundle#1', 'path#1', 'domain#1.fileName#1'),
            array('bundle#1', 'path#1', 'domain#2.fileName#2'),
            array('bundle#1', 'path#2', 'domain#1.fileName#3'),
            array('bundle#1', 'path#2', 'domain#3.fileName#4'),
            array('bundle#2', 'path#3', 'domain#1.fileName#5'),
            array('bundle#2', 'path#3', 'domain#2.fileName#6'),
            array('bundle#2', 'path#4', 'domain#1.fileName#7'),
            array('bundle#2', 'path#4', 'domain#3.fileName#8')
        );
    }

    /**
     * Get configured translation test object
     *
     * @return Translations
     */
    protected function getConfiguredTranslationsTestObject()
    {
        $sourceTrans = $this->getTranslationsSourceTestData();
        $sourceFiles = $this->getFilesSourceTestData();

        $translations = new Translations();

        foreach ($sourceTrans as $item) {
            $translations->addTranslation($item[0], $item[1], $item[2], $item[3], $item[4]);
        }

        foreach ($sourceFiles as $item) {
            $translations->addFile($item[0], $item[1], $item[2]);
        }

        return $translations->sort();
    }
}
