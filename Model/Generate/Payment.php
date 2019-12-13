<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sagar Bathla
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Webkul\CodeGenerator\Model\Helper;
use Webkul\CodeGenerator\Api\GenerateInterface;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Simplexml\Config;

/**
 * Class Payment.php
 */
class Payment implements GenerateInterface
{
    const SYSTEMXML_NODE = '//section[@id="payment"]';
    const CONFIGXML_NODE = '//payment';

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
        
        $path = $data['path'];
        
        Helper::createDirectory(
            $paymentModelDirPath = $path.DIRECTORY_SEPARATOR.'Model'
        );
        Helper::createDirectory(
            $configDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        Helper::createDirectory(
            $path.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.'adminhtml'
        );
        Helper::createDirectory(
            $jsDirPath = $path.DIRECTORY_SEPARATOR.'view/frontend/web/js/view/payment'
        );
        Helper::createDirectory(
            $templateDirPath = $path.DIRECTORY_SEPARATOR.'view/frontend/web/template/payment'
        );

        Helper::createDirectory(
            $layoutDirPath = $path.DIRECTORY_SEPARATOR.'view/frontend/layout'
        );
        
        $this->createSystemXml($configDirPath, $path, $data);
        $this->createConfigXml($configDirPath, $path, $data);
        $this->createPaymentModelClass($paymentModelDirPath, $data);
        $this->createPaymentRenderer($jsDirPath, $data);
        $this->createPaymentJs($jsDirPath, $data);
        $this->createPaymentTemplate($templateDirPath, $data);
        $this->createCheckoutLayout($layoutDirPath, $data);
        return ['status' => 'success', 'message' => "Payment method generated successfully"];
    }

    /**
     * Create system.xml
     *
     * @param string $etcAdminthtmlDirPath
     * @param string $moduleDir
     * @return void
     */
    public function createSystemXml($configDirPath, $moduleDir, $data)
    {
        $systemXml = $this->getSystemXmlFile($moduleDir);
        if (!$systemXml) {
            $systemXml = $this->createSystemXmlFile($moduleDir);
        }
        $this->addNewPaymentData($systemXml, $data);
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
        $paymentModel = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$className ;
        $xmlObj = new Config($configXmlFile);
        $configXml = $xmlObj->getNode();
        if (!$configXml->default) {
            throw new \RuntimeException(
                __('Incorrect config.xml schema found')
            );
        }
        $paymentNode = $configXml->xpath(self::CONFIGXML_NODE);
        if (!isset($paymentNode[0]) || !$paymentNode[0]) {
            $paymentNode = $this->xmlGenerator->addXmlNode($configXml->default, 'payment');
            $paymentNode = $configXml->xpath(self::CONFIGXML_NODE);
        }
        $isUniqueNode = true;
        $codeNode = $this->xmlGenerator->addXmlNode($paymentNode[0], $code, null, null, false, $isUniqueNode);
        $this->xmlGenerator->addXmlNode($codeNode, 'active', "1");
        $this->xmlGenerator->addXmlNode($codeNode, 'payment_action', "authorize");
        $this->xmlGenerator->addXmlNode($codeNode, 'model', $paymentModel);
        $this->xmlGenerator->addXmlNode($codeNode, 'title', $name);
        $this->xmlGenerator->addXmlNode($codeNode, 'order_status', 'pending_payment');
        $xmlData = $this->xmlGenerator->formatXml($configXml->asXml());
        $this->helper->saveFile($configXmlFile, $xmlData);
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
     * Add new payment group
     *
     * @param string $systemXmlFile
     * @param array $data
     * @return void
     */
    private function addNewPaymentData($systemXmlFile, $data)
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
            $this->defaultAttribute['id'] = 'payment';
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
        $fieldsData = $this->getPaymentFieldsData();
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
     * Get default field data.
     *
     * @return array
     */
    private function getPaymentFieldsData()
    {
        $paymentData = $this->jsonHelper->unserialize(
            $this->getPaymentDataTemplate()
        );
        return $paymentData;
    }

    /**
     * get module.xml template
     *
     * @return string
     */
    protected function getPaymentDataTemplate() : string
    {
        // @codingStandardsIgnoreStart
        return file_get_contents(
            dirname(dirname( dirname(__FILE__) )) . '/templates/payment/payment_fields.json'
        );
        // @codingStandardsIgnoreEnd
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
     * Create system.xml
     *
     * @param string $moduleDir
     * @return void
     */
    private function createSystemXmlFile($moduleDir)
    {
        $systemXmlFilePath = $this->getSystemXmlFilePath($moduleDir);
        // @codingStandardsIgnoreStart
        $paymentXmlData = file_get_contents(dirname(dirname( dirname(__FILE__) )) . '/templates/system.xml.dist');
        // @codingStandardsIgnoreEnd
        $this->helper->saveFile($systemXmlFilePath, $paymentXmlData);
        return $systemXmlFilePath;
    }

    /**
     * create payment model class
     *
     * @param [type] $dir
     * @param [type] $data
     * @return void
     */
    public function createPaymentModelClass($dir, $data)
    {
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model';
        $parentClass = \Magento\Payment\Model\Method\AbstractMethod::class;
        $modelClass      = new ClassGenerator();

        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $paymentMethodCode = strtolower($data['code']);

        $modelClass->setName($className)
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $className.' Class',
        ]))
        ->addProperties([
            ['_code', $paymentMethodCode, PropertyGenerator::FLAG_PROTECTED]
        ])
        ->setExtendedClass($parentClass);

        $file = new \Zend\Code\Generator\FileGenerator([
            'classes'  => [$modelClass],
            'docblock' => $docblock
        ]);
        
        // or write it to a file:
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$className.'.php',
            $file->generate()
        );
    }

    public function createPaymentRenderer($dir, $data)
    {
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $rendererTemplate = $this->helper->getTemplatesFiles('templates/payment/method-renderer.js.dist');
        $rendererTemplate = str_replace('%paymentCode%', strtolower($className), $rendererTemplate);
        $rendererTemplate = str_replace(
            '%moduleName%',
            $moduleNamespace[0].'_'.$moduleNamespace[1],
            $rendererTemplate
        );
        $rendererFile = $dir . '/method-renderer.js';
        // or write it to a file:
        $this->helper->saveFile(
            $rendererFile,
            $rendererTemplate
        );
    }

    public function createPaymentJs($dir, $data)
    {
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $paymentJsTemplate = $this->helper->getTemplatesFiles('templates/payment/payment.js.dist');
        $paymentJsTemplate = str_replace('%paymentCode%', strtolower($className), $paymentJsTemplate);
        $paymentJsTemplate = str_replace(
            '%moduleName%',
            $moduleNamespace[0].'_'.$moduleNamespace[1],
            $paymentJsTemplate
        );
        $paymentJsFile = $dir . '/method-renderer/'.strtolower($className).'.js';
        $this->helper->createDirectory($dir . '/method-renderer');
        // or write it to a file:
        $this->helper->saveFile(
            $paymentJsFile,
            $paymentJsTemplate
        );
    }

    public function createPaymentTemplate($dir, $data)
    {
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $paymentTemplate = $this->helper->getTemplatesFiles('templates/payment/paymentTemplate.html.dist');
        $paymentFile = $dir . '/'.strtolower($className).'.html';
        // or write it to a file:
        $this->helper->saveFile(
            $paymentFile,
            $paymentTemplate
        );
    }

    public function createCheckoutLayout($dir, $data)
    {
        $className = $this->getClassName($data['code']);
        $moduleNamespace = explode('_', $data['module']);
        $paymentLayoutTemplate = $this->helper->getTemplatesFiles('templates/payment/checkout.xml.dist');
        $paymentLayoutTemplate = str_replace('%paymentCode%', strtolower($className), $paymentLayoutTemplate);
        $paymentLayoutTemplate = str_replace(
            '%moduleName%',
            $moduleNamespace[0].'_'.$moduleNamespace[1],
            $paymentLayoutTemplate
        );
        $paymentLayoutFile = $dir . '/checkout_index_index.xml';
        // or write it to a file:
        $this->helper->saveFile(
            $paymentLayoutFile,
            $paymentLayoutTemplate
        );
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
