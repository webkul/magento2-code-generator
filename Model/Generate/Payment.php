<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sagar Bathla
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
use Zend\Config\Config;
use Zend\Config\Writer\Xml;
use XMLWriter;
use Zend\Stdlib\ArrayUtils;

/**
 * Class Payment.php
 */
class Payment implements GenerateInterface
{

    protected $readerComposite;

    protected $helper;

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
        
        Helper::createDirectory(
            $paymentModelDirPath = $path.DIRECTORY_SEPARATOR.'Model'
        );
        Helper::createDirectory(
            $configDirPath = $path.DIRECTORY_SEPARATOR.'etc'
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
        $this->createConfigFile($configDirPath, $data);
        $this->createSystemFile($configDirPath, $data);
        $this->createPaymentModelClass($paymentModelDirPath, $data);
        $this->createPaymentRenderer($jsDirPath, $data);
        $this->createPaymentJs($jsDirPath, $data);
        $this->createPaymentTemplate($templateDirPath, $data);
        $this->createCheckoutLayout($layoutDirPath, $data);
        return ['status' => 'success', 'message' => "Payment method generated successfully"];
    }

    public function createConfigFile($dir, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'];
        $configXmlTemplate = $this->helper->getTemplatesFiles('templates/payment/config.xml.dist');
        $configXmlTemplate = str_replace('%paymentCode%', strtolower($data['name']), $configXmlTemplate);
        $configXmlTemplate = str_replace('%paymentModel%', $nameSpace, $configXmlTemplate);
        $configXmlTemplate = str_replace('%paymentTitle%', $data['name'], $configXmlTemplate);
        $configXmlFile = $dir . '/config.xml';
        // or write it to a file:
        $this->helper->saveFile(
            $configXmlFile,
            $configXmlTemplate
        );
    }

    public function createSystemFile($dir, $data)
    {
        $systemXmlTemplate = $this->helper->getTemplatesFiles('templates/payment/system.xml.dist');
        $systemXmlTemplate = str_replace('%paymentCode%', strtolower($data['name']), $systemXmlTemplate);
        $systemXmlFile = $dir . '/adminhtml/system.xml';
        $this->helper->createDirectory($dir . '/adminhtml');
        // or write it to a file:
        $this->helper->saveFile(
            $systemXmlFile,
            $systemXmlTemplate
        );
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
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model';
        $parentClass = "Magento\\Payment\\Model\\Method\\AbstractMethod";
        $modelClass      = new ClassGenerator();

        $docblock = $this->helper->getHeadDocBlock($data['module']);

        $paymentMethodCode = strtolower($data['name']);

        $modelClass->setName($data['name'])
        ->setNameSpaceName($nameSpace)
        ->setDocblock(DocBlockGenerator::fromArray([
            'shortDescription' => $data['name'].' Class',
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
            $dir.DIRECTORY_SEPARATOR.$data['name'].'.php',
            $file->generate()
        );
    }

    public function createPaymentRenderer($dir, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'];
        $rendererTemplate = $this->helper->getTemplatesFiles('templates/payment/method-renderer.js.dist');
        $rendererTemplate = str_replace('%paymentCode%', strtolower($data['name']), $rendererTemplate);
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
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'];
        $paymentJsTemplate = $this->helper->getTemplatesFiles('templates/payment/payment.js.dist');
        $paymentJsTemplate = str_replace('%paymentCode%', strtolower($data['name']), $paymentJsTemplate);
        $paymentJsTemplate = str_replace(
            '%moduleName%',
            $moduleNamespace[0].'_'.$moduleNamespace[1],
            $paymentJsTemplate
        );
        $paymentJsFile = $dir . '/method-renderer/'.strtolower($data['name']).'.js';
        $this->helper->createDirectory($dir . '/method-renderer');
        // or write it to a file:
        $this->helper->saveFile(
            $paymentJsFile,
            $paymentJsTemplate
        );
    }

    public function createPaymentTemplate($dir, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'];
        $paymentTemplate = $this->helper->getTemplatesFiles('templates/payment/paymentTemplate.html.dist');
        $paymentFile = $dir . '/'.strtolower($data['name']).'.html';
        // or write it to a file:
        $this->helper->saveFile(
            $paymentFile,
            $paymentTemplate
        );
    }

    public function createCheckoutLayout($dir, $data)
    {
        $moduleNamespace = explode('_', $data['module']);
        $nameSpace = $moduleNamespace[0].'\\'.$moduleNamespace[1].'\\Model\\'.$data['name'];
        $paymentLayoutTemplate = $this->helper->getTemplatesFiles('templates/payment/checkout.xml.dist');
        $paymentLayoutTemplate = str_replace('%paymentCode%', strtolower($data['name']), $paymentLayoutTemplate);
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
}
