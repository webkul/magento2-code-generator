<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Mahesh Singh
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Api\GenerateInterface;
use Webkul\CodeGenerator\Model\Helper;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Simplexml\Config;
use Magento\Framework\Simplexml\Element;

/**
 * Class Shipping to generate shipping method files
 */
class Shipping implements GenerateInterface
{
    const SYSTEMXML_NODE = '//section[@id="carriers"]';
    const CONFIGXML_NODE = '//carriers';

    protected $readerComposite;

    protected $helper;

    /**
     * Default xml node attributes
     *
     * @var array
     */
    protected $defaultAttribute = [
        'id' => '',
        'translate' => 'label',
        'type' => 'text',
        'sortOrder' => "0",
        'showInDefault' => "1",
        'showInWebsite' => "1",
        'showInStore' => "1"
    ];

    /**
     * @param ReaderComposite $readerComposite
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param XmlGeneratorFactory $xmlGeneratorFactory
     * @param Json $jsonHelper
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Io\File $file,
        XmlGeneratorFactory $xmlGeneratorFactory,
        Json $jsonHelper,
        Helper $helper
    ) {
        $this->fileDriver = $fileDriver;
        $this->file = $file;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->xmlGenerator = $xmlGeneratorFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $modelName = $data['name'];
        $path = $data['path'];

        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        Helper::createDirectory(
            $etcAdminthtmlDirPath = $path.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'adminhtml'
        );
        $this->createSystemXml($etcAdminthtmlDirPath, $path, $data);
        $this->createConfigXml($etcAdminthtmlDirPath, $path, $data);
        $this->createModelClass($path, $data);

        return ['status' => 'success', 'message' => "shipping code generated successfully"];
    }

    /**
     * Create system.xml
     *
     * @param string $etcAdminthtmlDirPath
     * @param string $moduleDir
     * @return void
     */
    public function createSystemXml($etcAdminthtmlDirPath, $moduleDir, $data)
    {
        $systemXml = $this->getSystemXmlFile($moduleDir);
        if (!$systemXml) {
            $systemXml = $this->createSystemXmlFile($moduleDir);
        }
        $this->addNewShippingData($systemXml, $data);
    }

    /**
     * Create config.xml file
     *
     * @param string $etcAdminthtmlDirPath
     * @param string $moduleDir
     * @param array $data
     * @return void
     */
    public function createConfigXml($etcAdminthtmlDirPath, $moduleDir, $data)
    {
        $configXml = $this->getConfigXmlFile($moduleDir);
        if (!$configXml) {
            $configXml = $this->createConfigXmlFile($moduleDir);
        }
        $this->addConfigXmlData($configXml, $data);
    }

    /**
     * Get if system.xml already exists
     *
     * @param string $modelDirPath
     * @return string|bool
     */
    private function getSystemXmlFile($modelDirPath)
    {
        $systemXmlFilePath = $this->getSystemXmlFilePath($modelDirPath);
        if ($this->fileDriver->isExists($systemXmlFilePath)) {
            return $systemXmlFilePath;
        }
        return false;
    }

    /**
     * Get if config.xml already exists
     *
     * @param string $modelDirPath
     * @return string|bool
     */
    private function getConfigXmlFile($modelDirPath)
    {
        $configXmlFilePath = $this->getConfigXmlFilePath($modelDirPath);
        if ($this->fileDriver->isExists($configXmlFilePath)) {
            return $configXmlFilePath;
        }
        return false;
    }

    /**
     * Create system.xml
     *
     * @param string $moduleDir
     * @return void
     */
    private function createSystemXmlFile($moduleDir)
    {
        $systemXmlFilePath = $this->getSystemXmlFilePath($moduleDir);
        // @codingStandardsIgnoreStart
        $shippingXmlData = file_get_contents(dirname(dirname( dirname(__FILE__) )) . '/templates/system.xml.dist');
        // @codingStandardsIgnoreEnd
        $this->helper->saveFile($systemXmlFilePath, $shippingXmlData);
        return $systemXmlFilePath;
    }

    /**
     * Create config.xml
     *
     * @param string $moduleDir
     * @return void
     */
    private function createConfigXmlFile($moduleDir)
    {
        $configXmlFilePath = $this->getConfigXmlFilePath($moduleDir);
        // @codingStandardsIgnoreStart
        $configXmlData = file_get_contents(dirname(dirname( dirname(__FILE__) )) . '/templates/config.xml.dist');
        // @codingStandardsIgnoreEnd
        $this->helper->saveFile($configXmlFilePath, $configXmlData);
        return $configXmlFilePath;
    }

    /**
     * Add new shipping group
     *
     * @param string $systemXmlFile
     * @param array $data
     * @return void
     */
    private function addNewShippingData($systemXmlFile, $data)
    {
        $name = $data['name'];
        $path = $data['path'];
        $code = $data['code'];

        $xmlObj = new Config($systemXmlFile);
        $systemXml = $xmlObj->getNode();
        $sectionNode = $systemXml->xpath(self::SYSTEMXML_NODE);

        if (!$systemXml->system) {
            throw new \RuntimeException(
                __('Incorrect system.xml schema found')
            );
        }
        if (!isset($sectionNode[0]) || !$sectionNode[0]) {
            $this->defaultAttribute['id'] = 'carriers';
            $this->defaultAttribute['sortOrder'] = "999";
            unset($this->defaultAttribute['translate']);

            $attributes = $this->defaultAttribute;
            $sectionNode = $this->xmlGenerator->addXmlNode($systemXml->system, 'section', '', $attributes);
            $sectionNode = $systemXml->xpath(self::SYSTEMXML_NODE);
        }
        $groupNode = $this->addGroupNode($sectionNode[0], $data);
        $this->addFieldNodes($groupNode);
        $xmlData = $this->xmlGenerator->formatXml($systemXml->asXml());
        $this->helper->saveFile($systemXmlFile, $xmlData);
    }

    /**
     * Generate config.xml file
     *
     * @param string $configXmlFile
     * @param array $data
     * @return void
     */
    private function addConfigXmlData($configXmlFile, $data)
    {
        $name = $data['name'];
        $path = $data['path'];
        $code = $data['code'];
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $shippingModel = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$className.'\\'.'Carrier';
        $xmlObj = new Config($configXmlFile);
        $configXml = $xmlObj->getNode();
        if (!$configXml->default) {
            throw new \RuntimeException(
                __('Incorrect config.xml schema found')
            );
        }
        $carriersNode = $configXml->xpath(self::CONFIGXML_NODE);
        if (!isset($carriersNode[0]) || !$carriersNode[0]) {
            $carriersNode = $this->xmlGenerator->addXmlNode($configXml->default, 'carriers');
            $carriersNode = $configXml->xpath(self::CONFIGXML_NODE);
        }
        $isUniqueNode = true;
        $codeNode = $this->xmlGenerator->addXmlNode($carriersNode[0], $code, null, null, false, $isUniqueNode);
        $this->xmlGenerator->addXmlNode($codeNode, 'active', "1");
        $this->xmlGenerator->addXmlNode($codeNode, 'sallowspecific', "0");
        $this->xmlGenerator->addXmlNode($codeNode, 'model', $shippingModel);
        $this->xmlGenerator->addXmlNode($codeNode, 'price', '5.00');
        $this->xmlGenerator->addXmlNode($codeNode, 'title', $name);
        $this->xmlGenerator->addXmlNode($codeNode, 'specificerrmsg', 'Not able to load shipping cost.');
        $xmlData = $this->xmlGenerator->formatXml($configXml->asXml());
        $this->helper->saveFile($configXmlFile, $xmlData);
    }

    /**
     * Get default field data.
     *
     * @return array
     */
    private function getShippingFieldsData()
    {
        $shippingData = $this->jsonHelper->unserialize(
            $this->getShippingDataTemplate()
        );
        return $shippingData;
    }

    /**
     * get module.xml template
     *
     * @return string
     */
    protected function getShippingDataTemplate() : string
    {
        // @codingStandardsIgnoreStart
        return file_get_contents(
            dirname(dirname( dirname(__FILE__) )) . '/templates/shipping/shipping_fields.json'
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * Add <group> node
     *
     * @param Element $sectionNode
     * @param array $data
     * @return void
     */
    private function addGroupNode($sectionNode, array $data)
    {
        $name = $data['name'];
        $code = $data['code'];
        $this->defaultAttribute['id'] = $code;
        $this->defaultAttribute['sortOrder'] = '999';
        $groupNode = $this->xmlGenerator->addXmlNode($sectionNode, 'group', '', $this->defaultAttribute, 'id');
        $this->xmlGenerator->addXmlNode($groupNode, 'label', $name, [], false);

        return $groupNode;
    }

    /**
     * Add <field> node
     *
     * @param Element $groupNode
     * @return void
     */
    private function addFieldNodes($groupNode)
    {
        $fieldsData = $this->getShippingFieldsData();
        foreach ($fieldsData as $fieldData) {
            $this->xmlGenerator->addXmlNode(
                $groupNode,
                'field',
                $fieldData['value'],
                $fieldData['attribute'],
                'id'
            );
        }
        return $groupNode;
    }

    /**
     * Get system.xml file path
     *
     * @param string $modelDirPath
     * @return string
     */
    private function getSystemXmlFilePath($modelDirPath)
    {
        return $modelDirPath.DIRECTORY_SEPARATOR.'etc/adminhtml/system.xml';
    }

    /**
     * Get config.xml file path
     *
     * @param string $modelDirPath
     * @return string
     */
    private function getConfigXmlFilePath($modelDirPath)
    {
        return $modelDirPath.DIRECTORY_SEPARATOR.'etc/config.xml';
    }

    /**
     * create model class
     *
     * @param [type] $dir
     * @param [type] $data
     * @return void
     */
    public function createModelClass($path, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $className = $this->getClassName($data['code']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$className;
        $modelClass = $this->getShippingOfflineModelClassTemplate();
        $modelClass = str_replace('%code%', $data['code'], $modelClass);
        $modelClass = str_replace('%namespace%', $nameSpace, $modelClass);
        Helper::createDirectory(
            $modelDirPath = $path.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.$className
        );
        // or write it to a file:
        $this->helper->saveFile(
            $modelDirPath.DIRECTORY_SEPARATOR.'Carrier.php',
            $modelClass
        );
    }

    /**
     * get module.xml template
     *
     * @return string
     */
    protected function getShippingOfflineModelClassTemplate() : string
    {
        // @codingStandardsIgnoreStart
        return file_get_contents(
            dirname(dirname( dirname(__FILE__) )) . '/templates/shipping/offline_shipping.php.dist'
        );
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get class name
     *
     * @param string $code
     * @return string
     */
    private function getClassName($code)
    {
        $fields = explode('_', $code);
        $className = ucfirst($code);
        if (count($fields) > 1) {
            $className = '';
            foreach ($fields as $key => $f) {
                if ($key == 0) {
                    $camelCase = ucfirst($f);
                } else {
                    $camelCase.= ucfirst($f);
                }
            }
            $className = $camelCase;
        }
        return $className;
    }
}
