<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Api\GenerateInterface;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;
use Webkul\CodeGenerator\Model\Helper;

/**
 * Generate Model.php
 */
class Model implements GenerateInterface
{

    /**
     * @var ReaderComposite
     */
    protected $readerComposite;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * __construct function
     *
     * @param ReaderComposite $readerComposite
     * @param Helper $helper
     */
    public function __construct(
        ReaderComposite $readerComposite,
        Helper $helper
    ) {
        $this->readerComposite = $readerComposite;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $modelName = $data['name'];
        $path = $data['path'];
        $identityColumn = $data['identity']??'id';
        $$identityColumn = &$identityColumn;
        $columns = [];
        if (isset($data['table']) && $data['table']) {

            $tableData = $this->readerComposite->read($data['module']);
            if (isset($tableData['table'][$data['table']])) {
                $columns = $tableData['table'][$data['table']]['column'];

                foreach ($columns as $key => $column) {
                    if (isset($column['identity']) && $column['identity'] == 'true') {
                        $identityColumn = $key;
                    }
                }
            }
        }

        $this->helper->createDirectory(
            $modelDirPath = $path.DIRECTORY_SEPARATOR.'Model'
        );
        $this->helper->createDirectory(
            $rModelDirPath = $path.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'ResourceModel'
        );
        $this->helper->createDirectory(
            $collectionDirPath = $path.
                DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'ResourceModel'.DIRECTORY_SEPARATOR.$modelName
        );
        $this->helper->createDirectory(
            $apiDataDirPath = $path.DIRECTORY_SEPARATOR.'Api'
        );
        $this->helper->createDirectory(
            $apiDataDirPath = $apiDataDirPath.DIRECTORY_SEPARATOR.'Data'
        );

        $this->createApiClass($apiDataDirPath, $data, $columns);
        $this->createModelClass($modelDirPath, $data, $columns);
        $this->createResourceModelClass($rModelDirPath, $data, $identityColumn);
        $this->createCollectionClass($collectionDirPath, $data, $identityColumn);

        return ['status' => 'success', 'message' => "model generated successfully"];
    }

    /**
     * Create api contract
     *
     * @param string $dir
     * @param array $data
     * @param array $columns
     * @return void
     */
    public function createApiClass($dir, $data, $columns)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Api\\Data';
        $generatorsMethods = [];
        $constants = [];
        if (count($columns) > 0) {
            foreach ($columns as $field => $column) {
                $fields = explode('_', $field);
                $fieldName = ucfirst($field);
                $camelCase = $field;
                if (count($fields) > 1) {
                    $fieldName = '';
                    foreach ($fields as $key => $f) {
                        if ($key == 0) {
                            $camelCase = $f;
                        } else {
                            $camelCase.= ucfirst($f);
                        }
                        $fieldName.=ucfirst($f);
                    }
                }
                array_push($constants, [
                    strtoupper($field),
                    $field,
                ]);
                array_push($generatorsMethods, [
                    'set'.$fieldName,
                    [$camelCase],
                    MethodGenerator::FLAG_INTERFACE,
                    null,
                    DocBlockGenerator::fromArray([
                        'shortDescription' => 'Set '.$fieldName,
                        'longDescription'  => "",
                        'tags'             => [
                            new Tag\ParamTag($camelCase, [$this->helper->getReturnType(
                                $column['type']
                            )]),
                            new Tag\ReturnTag([
                                'datatype'  => '\\'.$nameSpace.'\\'.$data['name'].'Interface',
                            ]),
                        ],
                    ]),
                ]);
                array_push($generatorsMethods, [
                    'get'.$fieldName,
                    [],
                    MethodGenerator::FLAG_INTERFACE,
                    null,
                    'docblock'   => DocBlockGenerator::fromArray([
                        'shortDescription' => 'Get '.$fieldName,
                        'longDescription'  => "",
                        'tags'             => [
                            new Tag\ReturnTag([
                                'datatype'  => $this->helper->getReturnType($column['type']),
                            ]),
                        ],
                    ]),
                ]);
            }
        }

        try {
            $apiClass = \Laminas\Code\Generator\InterfaceGenerator::fromArray([
                'name' => $data['name'].'Interface',
                'namespacename' => $nameSpace,
                'docblock'  => [
                    'shortDescription' => $data['name'].' Model Interface',
                ],
                'constants' => $constants,
                'methods' => $generatorsMethods
            ]);

            $file = new \Laminas\Code\Generator\FileGenerator([
                'classes'  => [$apiClass],
                'docblock' => $this->helper->getHeadDocBlock($data['module'])
            ]);

            // or write it to a file:
            $this->helper->saveFile(
                $dir.DIRECTORY_SEPARATOR.$data['name'].'Interface'.'.php',
                $file->generate()
            );
        } catch (\Exception $e) {
            $ex = $e->getMessage();
        }
    }

    /**
     * Create model class
     *
     * @param string $dir
     * @param array $data
     * @param array $columns
     * @return void
     */
    public function createModelClass($dir, $data, $columns)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model';
        $parentClass = "Magento\\Framework\\Model\\AbstractModel";
        $parentInterface = "Magento\\Framework\\DataObject\\IdentityInterface";
        $apiInterface = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Api\\Data\\'.$data['name'].'Interface';
        $resourceClass = '\\'.$nameSpace.'\\ResourceModel\\'.$data['name'];
        $modelClass      = new ClassGenerator();

        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $cacheTag = strtolower($data['module']).'_'.strtolower($data['name']);

        $generatorsMethods = [
            // Method passed as array
            MethodGenerator::fromArray([
                'name'       => '_construct',
                'parameters' => [],
                'body'       => '$this->_init('.$resourceClass.'::class);',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Set resource model',
                    'longDescription'  => "",
                ]),
            ]),
            // MethodGenerator::fromArray([
            //     'name'       => 'load',
            //     'parameters' => ['id', 'field'],
            //     'body'       => 'if ($id === null) {'. "\n". 'return $this->noRouteReasons();'. "\n". '}'. "\n". 'return parent::load($id, $field);',
            //     'docblock'   => DocBlockGenerator::fromArray([
            //         'shortDescription' => 'load model',
            //         'longDescription'  => "",
            //         'tags'             => [
            //             new Tag\ParamTag('id', ['int']),
            //             new Tag\ParamTag('field', ['string']),
            //             new Tag\ReturnTag([
            //                 'datatype'  => '$this',
            //             ]),
            //         ],
            //     ]),
            // ]),
            MethodGenerator::fromArray([
                'name'       => 'noRouteReasons',
                'parameters' => [],
                'body'       => 'return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Load No-Route Indexer.',
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => '$this',
                        ]),
                    ],
                ]),
            ]),
            MethodGenerator::fromArray([
                'name'       => 'getIdentities',
                'parameters' => [],
                'body'       => 'return [self::CACHE_TAG.\'_\'.$this->getId()];',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Get identities.',
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => '[]',
                        ]),
                    ],
                ]),
            ])
        ];

        foreach ($columns as $field => $column) {
            $fields = explode('_', $field);
            $fieldName = ucfirst($field);
            $camelCase = $field;
            if (count($fields) > 1) {
                $fieldName = '';
                foreach ($fields as $key => $f) {
                    if ($key == 0) {
                        $camelCase = $f;
                    } else {
                        $camelCase.= ucfirst($f);
                    }
                    $fieldName.=ucfirst($f);
                }
            }
            
            array_push($generatorsMethods, [
                'set'.$fieldName,
                [$camelCase],
                MethodGenerator::FLAG_PUBLIC,
                'return $this->setData(self::'.strtoupper($field).', $'.$camelCase.');',
                DocBlockGenerator::fromArray([
                    'shortDescription' => 'Set '.$fieldName,
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ParamTag($camelCase, [$this->helper->getReturnType($column['type'])]),
                        new Tag\ReturnTag([
                            'datatype'  => '\\'.$nameSpace.'\\'.$data['name'].'Interface',
                        ]),
                    ],
                ]),
            ]);
            array_push($generatorsMethods, [
                'get'.$fieldName,
                [],
                MethodGenerator::FLAG_PUBLIC,
                'return parent::getData(self::'.strtoupper($field).');',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Get '.$fieldName,
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => $this->helper->getReturnType($column['type']),
                        ]),
                    ],
                ]),
            ]);
        }

        $modelClass->setName($data['name'])
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $data['name'].' Model Class',
        ]))
        ->addProperties([
            ['_cacheTag', $cacheTag, PropertyGenerator::FLAG_PROTECTED],
            ['_eventPrefix',  $cacheTag, PropertyGenerator::FLAG_PROTECTED]
        ])
        ->addConstants([
            ['NOROUTE_ENTITY_ID', 'no-route', PropertyGenerator::FLAG_CONSTANT],
            ['CACHE_TAG', $cacheTag, PropertyGenerator::FLAG_CONSTANT]
        ])
        ->setExtendedClass($parentClass)
        ->setImplementedInterfaces([$parentInterface, $apiInterface])
        ->addMethods($generatorsMethods);

        $file = new \Laminas\Code\Generator\FileGenerator([
            'classes'  => [$modelClass],
            'docblock' => $docblock
        ]);

        // or write it to a file:
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$data['name'].'.php',
            $file->generate()
        );
    }

    /**
     * Generate resource model
     *
     * @param string $rModelDirPath
     * @param array $data
     * @param string $identityColumn
     * @return void
     */
    public function createResourceModelClass($rModelDirPath, $data, $identityColumn)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\ResourceModel';
        $parentClass = \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class;

        $generatorsMethods = [
            // Method passed as array
            MethodGenerator::fromArray([
                'name'       => '_construct',
                'parameters' => [],
                'body'       => '$this->_init("'.$data['table'].'", "'.$identityColumn.'");',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Initialize resource model',
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => 'void',
                        ]),
                    ],
                ]),
            ]),
        ];

        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $modelClass = new ClassGenerator();
        $modelClass->setName($data['name'])
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $data['name'].' RosourceModel Class',
        ]))
        ->setExtendedClass($parentClass)
        ->addMethods($generatorsMethods);

        $file = new \Laminas\Code\Generator\FileGenerator([
            'classes'  => [$modelClass],
            'docblock' => $docblock
        ]);

        // or write it to a file:
        $this->helper->saveFile(
            $rModelDirPath.DIRECTORY_SEPARATOR.$data['name'].'.php',
            $file->generate()
        );
    }

    /**
     * Generate collection class
     *
     * @param string $collectionDirPath
     * @param array $data
     * @param string $identityColumn
     * @return void
     */
    public function createCollectionClass($collectionDirPath, $data, $identityColumn)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\ResourceModel\\'.$data['name'];
        $parentClass = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;
        $modelClass = '\\'.$moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'].'::class';
        $resourceModel =
        '\\'.$moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\ResourceModel\\'.$data['name'].'::class';
        $generatorsMethods = [
            // Method passed as array
            MethodGenerator::fromArray([
                'name'       => '_construct',
                'parameters' => [],
                'body'       => '$this->_init('."\n"."    ".$modelClass.','."\n"."    ".$resourceModel."\n"
                .');'."\n".
                    '$this->_map[\'fields\'][\'entity_id\'] = \'main_table.'.$identityColumn.'\';',
                'docblock'   => DocBlockGenerator::fromArray([
                    'shortDescription' => 'Initialize resource model',
                    'longDescription'  => "",
                    'tags'             => [
                        new Tag\ReturnTag([
                            'datatype'  => 'void',
                        ]),
                    ],
                ]),
            ]),
        ];

        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $modelClass = new ClassGenerator();
        $modelClass->setName('Collection')
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $data['name'].' Collection Class',
        ]))
        ->addProperties([
            ['_idFieldName', $identityColumn, PropertyGenerator::FLAG_PROTECTED],
        ])
        ->setExtendedClass($parentClass)
        ->addMethods($generatorsMethods);

        $file = new \Laminas\Code\Generator\FileGenerator([
            'classes'  => [$modelClass],
            'docblock' => $docblock
        ]);

        // or write it to a file:
        $this->helper->saveFile(
            $collectionDirPath.DIRECTORY_SEPARATOR.'Collection.php',
            $file->generate()
        );
    }
}
