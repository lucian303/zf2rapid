<?php
/**
 * ZF2rapid - Zend Framework 2 Rapid Development Tool
 *
 * @link      https://github.com/ZFrapid/zf2rapid
 * @copyright Copyright (c) 2014 - 2015 Ralf Eggert
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */
namespace ZF2rapid\Generator;

use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ValueGenerator;

/**
 * Class ModuleClassGenerator
 *
 * @package ZF2rapid\Generator
 */
class ModuleClassGenerator extends ClassGenerator
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @param null|string $moduleName
     * @param array       $config
     */
    public function __construct($moduleName, array $config = array())
    {
        // set config data
        $this->config = $config;

        // call parent constructor
        parent::__construct('Module', $moduleName);

        // add methods
        $this->addGetConfigMethod();
        $this->addGetAutoloaderConfigMethod();
        $this->addClassDocBlock($moduleName);
    }

    /**
     * Add a class doc block
     *
     * @param $moduleName
     */
    protected function addClassDocBlock($moduleName)
    {
        // check for api docs
        if ($this->config['flagAddDocBlocks']) {
            $this->setDocBlock(
                new DocBlockGenerator(
                    'Module ' . $moduleName,
                    'Sets up and configures the ' . $moduleName . ' module',
                    array(
                        new GenericTag('package', $moduleName),
                    )
                )
            );
        }
    }

    /**
     * Generate the getConfig() method
     *
     * @return void
     */
    protected function addGetConfigMethod()
    {
        // create method body
        $body = new ValueGenerator();
        $body->initEnvironmentConstants();
        $body->setValue(
            'include __DIR__ . \'/config/module.config.php\''
        );

        // create method
        $method = new MethodGenerator();
        $method->setName('getConfig');
        $method->setBody(
            'return ' . $body->generate() . ';'
            . AbstractGenerator::LINE_FEED
        );

        // check for api docs
        if ($this->config['flagAddDocBlocks']) {
            $method->setDocBlock(
                new DocBlockGenerator(
                    'Get module configuration',
                    'Reads the module configuration from the config/ directory',
                    array(
                        new ReturnTag(
                            array('array'), 'module configuration data'
                        ),
                    )
                )
            );
        }

        // add method
        $this->addMethodFromGenerator($method);
        $this->addUse('Zend\ModuleManager\Feature\ConfigProviderInterface');
        $this->setImplementedInterfaces(
            array_merge(
                $this->getImplementedInterfaces(),
                array('ConfigProviderInterface')
            )
        );
    }

    /**
     * Generate the getAutoloaderConfig() method
     *
     * @return void
     *
     * @todo Needs to add classmap autoloading
     */
    protected function addGetAutoloaderConfigMethod()
    {
        // set array data
        $array = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    '__NAMESPACE__ => __DIR__ . \'/src/\' . __NAMESPACE__',
                ),
            ),
        );

        // create method body
        $body = new ValueGenerator();
        $body->initEnvironmentConstants();
        $body->setValue($array);

        // create method
        $method = new MethodGenerator();
        $method->setName('getAutoloaderConfig');
        $method->setBody(
            'return ' . $body->generate() . ';'
            . AbstractGenerator::LINE_FEED
        );

        // check for api docs
        if ($this->config['flagAddDocBlocks']) {
            $method->setDocBlock(
                new DocBlockGenerator(
                    'Get module autoloader configuration',
                    'Sets up the module autoloader configuration',
                    array(
                        new ReturnTag(
                            array('array'), 'module autoloader configuration'
                        ),
                    )
                )
            );
        }

        // add method
        $this->addMethodFromGenerator($method);
        $this->addUse(
            'Zend\ModuleManager\Feature\AutoloaderProviderInterface'
        );
        $this->setImplementedInterfaces(
            array_merge(
                $this->getImplementedInterfaces(),
                array('AutoloaderProviderInterface')
            )
        );
    }


}