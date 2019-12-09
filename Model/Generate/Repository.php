<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Api\GenerateInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;
use Webkul\CodeGenerator\Model\Helper;

/**
 * Class Repository
 */
class Repository implements GenerateInterface
{

    protected $helper;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $repoName = $data['name'];
        $path = $data['path'];

        Helper::createDirectory(
            $modelDirPath = $path.DIRECTORY_SEPARATOR.'Model'
        );

        Helper::createDirectory(
            $apiDataDirPath = $path.DIRECTORY_SEPARATOR.'Api'
        );
       
        $this->createApiClass($apiDataDirPath, $data);
        $this->createRepositoryClass($modelDirPath, $data);
    
        return ['status' => 'success', 'message' => "Repository successfully generated"];
    }

    /**
     * create api contract
     *
     * @param string $dir
     * @param [] $data
     * @return void
     */
    public function createApiClass($dir, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Api';
        $modelClass = $data['model_class'];
        $collectionClass = $data['collection_class'];
        $generatorsMethods = [
            [
                'getById',
                ['id'],
                MethodGenerator::FLAG_INTERFACE,
                null,
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => $data['model_class'],
                        ]),
                    ],
                ]),
            ],

            [
                'save',
                [['name' => 'subject', 'type' => $modelClass]],
                MethodGenerator::FLAG_INTERFACE,
                null,
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => $data['model_class'],
                        ]),
                    ],
                ]),
            ],

            [
                'getList',
                [['name' => 'creteria', 'type' => \Magento\Framework\Api\SearchCriteriaInterface::class]],
                MethodGenerator::FLAG_INTERFACE,
                null,
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get list',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('creteria', [\Magento\Framework\Api\SearchCriteriaInterface::class]),
                        new Tag\ReturnTag([
                            'datatype'  => \Magento\Framework\Api\SearchResults::class,
                        ]),
                    ],
                ]),
            ],

            [
                'delete',
                [['name' => 'subject', 'type' => $modelClass]],
                MethodGenerator::FLAG_INTERFACE,
                null,
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'delete',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('subject', [$modelClass]),
                        new Tag\ReturnTag([
                            'datatype'  => 'boolean',
                        ]),
                    ],
                ]),
            ],

            [
                'deleteById',
                ['id'],
                MethodGenerator::FLAG_INTERFACE,
                null,
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'delete by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => 'boolean',
                        ]),
                    ],
                ]),
            ],
        ];
        $constants = [];
        try {
            $apiClass = \Zend\Code\Generator\InterfaceGenerator::fromArray([
                'name' => $data['name'].'Interface',
                'namespacename' => $nameSpace,
                'docblock'  => [
                    'shortDescription' => $data['name'].' Interface',
                ],
                'constants' => $constants,
                'methods' => $generatorsMethods
            ]);

            $file = new \Zend\Code\Generator\FileGenerator([
                'classes'  => [$apiClass],
                'docblock' => $this->helper->getHeadDocBlock($data['module'])
            ]);

            // or write it to a file:
            $this->helper->saveFile(
                $dir.DIRECTORY_SEPARATOR.$data['name'].'Interface'.'.php',
                $file->generate()
            );
        } catch (\Exception $e) {
            //throw new \Exception($e->getMessage());
            // print_r($e->getTrace());
            // die;
        }
    }

    /**
     * create repository class
     *
     * @param string $dir
     * @param string $data
     * @return void
     */
     public function createRepositoryClass($dir, $data)
     {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model';
        $apiInterface = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Api\\'.$data['name'].'Interface';
        $modelClass = $data['model_class'];
        $collectionClass = $data['collection_class'];
        $repositoryClass     = new ClassGenerator();
        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $generatorsMethods = [
            [
                '__construct',
                [
                    ['name' => 'modelFactory', 'type' => $modelClass.'Factory'],
                    ['name' => 'collectionFactory', 'type' => $collectionClass.'Factory']
                ],
                MethodGenerator::FLAG_PUBLIC,
                '$this->modelFactory = $modelFactory; '."\n".
                '$this->collectionFactory = $collectionFactory;',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'initialize',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('modelFactory', [$modelClass.'Factory']),
                        new Tag\ParamTag('collectionFactory', [$collectionClass.'Factory']),
                        new Tag\ReturnTag([
                            'datatype'  => 'void',
                        ]),
                    ],
                ]),
            ],

            [
                'getById',
                ['id'],
                MethodGenerator::FLAG_PUBLIC,
                '$model = $this->modelFactory->create()->load($id);'."\n".'if (!$model->getId()) { '."\n".
                    ' throw new \\Magento\\Framework\\Exception\\NoSuchEntityException(__(\'The CMS block with the "%1" ID doesn\\\'t exist.\', $id)); '."\n".' } '."\n".
                    'return $model;',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => $data['model_class'],
                        ]),
                    ],
                ]),
            ],

            [
                'save',
                [['name' => 'subject', 'type' => $modelClass]],
                MethodGenerator::FLAG_PUBLIC,
                'try { '."\n".' $subject->save(); '."\n".'} catch (\Exception $exception) { '."\n".' throw new \\Magento\\Framework\\Exception\\CouldNotSaveException(__($exception->getMessage())); '.
                    "\n".'} '."\n".' return $subject; '."\n".'',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => $data['model_class'],
                        ]),
                    ],
                ]),
            ],

            [
                'getList',
                [['name' => 'creteria', 'type' => \Magento\Framework\Api\SearchCriteriaInterface::class]],
                MethodGenerator::FLAG_PUBLIC,
                '$collection = $this->collectionFactory->create(); '."\n".' return $collection;',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'get list',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('creteria', [\Magento\Framework\Api\SearchCriteriaInterface::class]),
                        new Tag\ReturnTag([
                            'datatype'  => \Magento\Framework\Api\SearchResults::class,
                        ]),
                    ],
                ]),
            ],

            [
                'delete',
                [['name' => 'subject', 'type' => $modelClass]],
                MethodGenerator::FLAG_PUBLIC,
                'try { '."\n".
                '$subject->delete();'."\n".
                '} catch (\Exception $exception) {'."\n".
                'throw new \\Magento\\Framework\\Exception\\CouldNotDeleteException(__($exception->getMessage()));'."\n".
                '}'."\n".'return true;'."\n".'',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'delete',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('subject', [$modelClass]),
                        new Tag\ReturnTag([
                            'datatype'  => 'boolean',
                        ]),
                    ],
                ]),
            ],

            [
                'deleteById',
                ['id'],
                MethodGenerator::FLAG_PUBLIC,
                'return $this->delete($this->getById($id));',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'delete by id',
                    'longDescription'  => null,
                    'tags'             => [
                        new Tag\ParamTag('id', ['int']),
                        new Tag\ReturnTag([
                            'datatype'  => 'boolean',
                        ]),
                    ],
                ]),
            ],
        ];

        $repositoryClass->setName($data['name'])
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $data['name'].' Class',
        ]))
        ->addProperties([
            ['modelFactory', null, PropertyGenerator::FLAG_PROTECTED],
            ['collectionFactory', null , PropertyGenerator::FLAG_PROTECTED]
        ])
        ->setImplementedInterfaces([$apiInterface])
        ->addMethods($generatorsMethods);

        $file = new \Zend\Code\Generator\FileGenerator([
            'classes'  => [$repositoryClass],
            'docblock' => $docblock
        ]);

        // or write it to a file:
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$data['name'].'.php',
            $file->generate()
        );

     }
}
