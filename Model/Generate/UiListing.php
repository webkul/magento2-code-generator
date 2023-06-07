<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Magento\Framework\Simplexml\Config;
use Webkul\CodeGenerator\Api\GenerateInterface;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;
use Webkul\CodeGenerator\Model\Helper as CodeHelper;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;

/**
 * Generate UiListing
 */
class UiListing implements GenerateInterface
{
    /**
     * @var ReaderComposite
     */
    protected $readerComposite;

    /**
     * @var CodeHelper
     */
    protected $helper;

    /**
     * @var XmlGeneratorFactory
     */
    protected $xmlGenerator;

    /**
     * __construct function
     *
     * @param CodeHelper $helper
     * @param ReaderComposite $readerComposite
     * @param XmlGeneratorFactory $xmlGeneratorFactory
     */
    public function __construct(
        CodeHelper $helper,
        ReaderComposite $readerComposite,
        XmlGeneratorFactory $xmlGeneratorFactory,
    ) {
        $this->helper = $helper;
        $this->readerComposite = $readerComposite;
        $this->xmlGenerator = $xmlGeneratorFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $path = $data['path'];
        $this->helper->createDirectory(
            $lsitingDir = $path.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'adminhtml'
                .DIRECTORY_SEPARATOR.'ui_component'
        );
        $this->helper->createDirectory(
            $collectionDir = $path.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'ResourceModel'
                .DIRECTORY_SEPARATOR.$data['model_class_name'].DIRECTORY_SEPARATOR."Grid"
        );
        $this->helper->createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );

        $this->generateCollectionClass($collectionDir, $data);
        $this->addDiXmlData($etcDirPath, $data);
        $this->generateUiListing($lsitingDir, $data);
       
        return ['status' => 'success', 'message' => "ui component listing successfully generated."];
    }

    /**
     * Create Grid Collection class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function generateCollectionClass($dir, $data)
    {
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $collection = '\\'.$nameArray[0].'\\'.$nameArray[1].'\\'."Model".'\\'."ResourceModel".'\\'.
            $data['model_class_name'].'\\'."Collection";
        $collectionFile = $this->helper->getTemplatesFiles('templates/ui_component/collection.php.dist');
        $collectionFile = str_replace('%module_name%', $data['module'], $collectionFile);
        $collectionFile = str_replace(
            '%namespace%',
            $nameArray[0].'\\'.$nameArray[1].'\\'."Model".'\\'."ResourceModel".'\\'.$data['model_class_name'].'\\'.
                'Grid',
            $collectionFile
        );
        $collectionFile = str_replace('%collection%', $collection, $collectionFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.'Collection.php',
            $collectionFile
        );
    }

    /**
     * Add di xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addDiXmlData($etcDirPath, $data)
    {
        $moduleName = $data['module'];
        $data['collection-class'] = str_replace('_', '\\', $moduleName).'\\'.'Model'.'\\'.'ResourceModel'.'\\'.
            $data['model_class_name'].'\\'.'Grid'.'\\'.'Collection';
        $data['resourceModel'] = str_replace('_', '\\', $moduleName).'\\'.'Model'.'\\'.'ResourceModel'.'\\'.
            $data['model_class_name'];
        $diXmlFile = $this->helper->getDiXmlFile($etcDirPath, $data);
        $xmlObj = new Config($diXmlFile);
        $diXml = $xmlObj->getNode();
        $typeNode = $this->xmlGenerator->addXmlNode(
            $diXml,
            'type',
            '',
            ['name'=> \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory::class]
        );
        $argsNode = $this->xmlGenerator->addXmlNode($typeNode, 'arguments');
        $argNode = $this->xmlGenerator->addXmlNode(
            $argsNode,
            'argument',
            "",
            ['name'=>'collections', 'xsi:type'=>'array']
        );
        $this->xmlGenerator->addXmlNode(
            $argNode,
            'item',
            $data['collection-class'],
            ['name'=> $data['name'].'_data_source', 'xsi:type'=>'string']
        );
        $typeNode = $this->xmlGenerator->addXmlNode($diXml, 'type', '', ['name'=> $data['collection-class']]);
        $argsNode = $this->xmlGenerator->addXmlNode($typeNode, 'arguments');
        $this->xmlGenerator->addXmlNode(
            $argsNode,
            'argument',
            $data['table'],
            ['name'=>'mainTable', 'xsi:type'=>'string']
        );
        $this->xmlGenerator->addXmlNode(
            $argsNode,
            'argument',
            $data['table'].'_grid_collection',
            ['name'=>'eventPrefix', 'xsi:type'=>'string']
        );
        $this->xmlGenerator->addXmlNode(
            $argsNode,
            'argument',
            strtolower($moduleName).'_'.$data['table'],
            ['name'=>'eventObject', 'xsi:type'=>'string']
        );
        $this->xmlGenerator->addXmlNode(
            $argsNode,
            'argument',
            $data['resourceModel'],
            ['name'=>'resourceModel', 'xsi:type'=>'string']
        );
        $xmlData = $this->xmlGenerator->formatXml($diXml->asXml());
        $this->helper->saveFile($diXmlFile, $xmlData);
    }

    /**
     * Add di xml data
     *
     * @param string $lsitingDir
     * @param array $data
     * @return void
     */
    public function generateUiListing($lsitingDir, $data)
    {
        $replace = [
            "module_name" => $data['module'],
            "listing_name" => $data['name'],
            "columns_name" => $data['columns_name']
        ];
        $componentXml = $this->helper->loadTemplateFile(
            $lsitingDir,
            $data['name'].'.xml',
            'templates/ui_component/ui_component_listing.xml.dist',
            $replace
        );

        $xmlObj = new Config($componentXml);
        $listingXml = $xmlObj->getNode();

        $tableData = $this->readerComposite->read($data['module']);
        if (isset($tableData['table'][$data['table']])) {
            $columns = $tableData['table'][$data['table']]['column'];
            $columnsNode = $this->xmlGenerator->addXmlNode(
                $listingXml,
                'columns',
                "",
                ["name" => $data['columns_name']]
            );
            foreach ($columns as $key => $column) {
                if (isset($column['identity']) && $column['identity'] == 'true') {
                    $selectionCol = $this->xmlGenerator->addXmlNode(
                        $columnsNode,
                        'selectionsColumn',
                        "",
                        ['name'=>'ids', 'sortOrder'=>'10']
                    );
                    $setting = $this->xmlGenerator->addXmlNode(
                        $selectionCol,
                        'settings'
                    );
                    $this->xmlGenerator->addXmlNode(
                        $setting,
                        'indexField',
                        $column['name']
                    );
                    break;
                }
            }
            $sortOrder = 10;
            foreach ($columns as $key => $tableColumn) {
                $sortOrder = $sortOrder + 10;
                $column = $this->xmlGenerator->addXmlNode(
                    $columnsNode,
                    'column',
                    "",
                    ['name'=> $tableColumn['name'] , 'sortOrder'=> $sortOrder]
                );
                $setting = $this->xmlGenerator->addXmlNode(
                    $column,
                    'settings'
                );
                $this->xmlGenerator->addXmlNode(
                    $setting,
                    'filter',
                    $this->getFilterType($tableColumn)
                );
                $this->xmlGenerator->addXmlNode(
                    $setting,
                    'label',
                    $tableColumn['comment'] ?? ucwords(str_replace('_', ' ', $tableColumn['name']))
                );
            }
        }
        $xmlData = $this->xmlGenerator->formatXml($listingXml->asXml());
        $this->helper->saveFile($componentXml, $xmlData);
    }

    /**
     * Get Component Filter
     *
     * @param array $column
     * @return string
     */
    public function getFilterType($column)
    {
        $filter = [
            "datetime" => "dateRange",
            "int" => "text",
            "float" => "text",
            "varchar" => "text",
            "smallint" => "text",
            "timestamp" => "dateRange"
        ];
        if (isset($column['identity']) && $column['identity'] == "true") {
            $filterText = "textRange";
        } elseif ($column['type'] != "") {
            $filterText = $filter[$column['type']];
        } else {
            $filterText = "text";
        }
        return $filterText;
    }
}
