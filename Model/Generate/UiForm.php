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
class UiForm implements GenerateInterface
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
            $formDir = $path.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'adminhtml'
                .DIRECTORY_SEPARATOR.'ui_component'
        );
        $this->helper->createDirectory(
            $buttonDir = $path.DIRECTORY_SEPARATOR.'Block'.DIRECTORY_SEPARATOR.'Adminhtml'
                .DIRECTORY_SEPARATOR.'General'.DIRECTORY_SEPARATOR.'Edit'
        );
        $this->helper->createDirectory(
            $providerDir = $path.DIRECTORY_SEPARATOR.'Ui'.DIRECTORY_SEPARATOR.'DataProvider'
        );

        $this->generateDataProvider($providerDir, $data);
        $this->generateButtons($buttonDir, $data);
        $this->generateUiForm($formDir, $data);
       
        return ['status' => 'success', 'message' => "ui component form successfully generated."];
    }

    /**
     * Create Grid Collection class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function generateDataProvider($dir, $data)
    {
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $collection = $nameArray[0].'\\'.$nameArray[1].'\\'."Model".'\\'."ResourceModel".'\\'.
            $data['model_class_name'].'\\'."Collection";
        $dataProvider = $this->helper->getTemplatesFiles('templates/ui_component/dataProvider.php.dist');
        $dataProvider = str_replace('%module_name%', $data['module'], $dataProvider);
        $dataProvider = str_replace(
            '%namespace%',
            $nameArray[0].'\\'.$nameArray[1].'\\'."Ui".'\\'."DataProvider",
            $dataProvider
        );
        $dataProvider = str_replace('%collection%', $collection, $dataProvider);
        $dataProvider = str_replace('%class_name%', ucfirst($data['provider_name']), $dataProvider);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.ucfirst($data['provider_name']).'.php',
            $dataProvider
        );
    }

    /**
     * Create Form Buttons
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function generateButtons($dir, $data)
    {
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $nameSpace = $nameArray[0].'\\'.$nameArray[1].'\\'."Block".'\\'."Adminhtml".'\\'."General".'\\'."Edit";

        $genericButton = $this->helper->getTemplatesFiles('templates/button/genericButton.php.dist');
        $genericButton = str_replace('%module_name%', $data['module'], $genericButton);
        $genericButton = str_replace('%namespace%', $nameSpace, $genericButton);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.'GenericButton.php',
            $genericButton
        );

        $saveButton = $this->helper->getTemplatesFiles('templates/button/saveButton.php.dist');
        $saveButton = str_replace('%module_name%', $data['module'], $saveButton);
        $saveButton = str_replace('%namespace%', $nameSpace, $saveButton);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.'SaveButton.php',
            $saveButton
        );

        $backButton = $this->helper->getTemplatesFiles('templates/button/backButton.php.dist');
        $backButton = str_replace('%module_name%', $data['module'], $backButton);
        $backButton = str_replace('%namespace%', $nameSpace, $backButton);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.'BackButton.php',
            $backButton
        );
    }

    /**
     * Add Form xml data
     *
     * @param string $formDir
     * @param array $data
     * @return void
     */
    public function generateUiForm($formDir, $data)
    {
        $nameSpace = $data['module'];
        $formField = json_decode($data['form_field'], true);
        $nameArray = explode("_", $nameSpace);
        $replace = [
            "module_name" => $data['module'],
            "form_name" => $data['name'],
            "namespace" => '"'.$nameArray[0].'\\'.$nameArray[1],
            "data_provider" =>
                $nameArray[0].'\\'.$nameArray[1].'\\'."Ui".'\\'."DataProvider".'\\'.$data['provider_name']
        ];
        $componentXml = $this->helper->loadTemplateFile(
            $formDir,
            $data['name'].'.xml',
            'templates/ui_component/ui_component_form.xml.dist',
            $replace
        );

        $xmlObj = new Config($componentXml);
        $listingXml = $xmlObj->getNode();

        $fieldset = $this->xmlGenerator->addXmlNode(
            $listingXml,
            'fieldset',
            "",
            ["name" => strtolower($data['fieldset_name'])]
        );
        $setting = $this->xmlGenerator->addXmlNode(
            $fieldset,
            'settings'
        );
        $this->xmlGenerator->addXmlNode(
            $setting,
            'label',
            $data['fieldset_label'],
            ["translate" => "true"]
        );
        $i = 10;
        foreach ($formField as $field) {
            $xmlField = $this->xmlGenerator->addXmlNode(
                $fieldset,
                'field',
                "",
                ["name" => strtolower($field['field_name']), "formElement" => $field['field_type'], "sortorder" => $i]
            );
            $argument = $this->xmlGenerator->addXmlNode(
                $xmlField,
                'argument',
                "",
                ["name" => "data", "xsi:type" => "array"]
            );
            $items = $this->xmlGenerator->addXmlNode(
                $argument,
                'item',
                "",
                ["name" => "config", "xsi:type" => "array"]
            );
            $this->xmlGenerator->addXmlNode(
                $items,
                'item',
                $field['field_label'],
                ["name" => "label", "xsi:type" => "string", "translate" => "true"]
            );
            
            /* Add Field Setting */
            $settings = $this->xmlGenerator->addXmlNode(
                $xmlField,
                'settings'
            );
            $validation = $this->xmlGenerator->addXmlNode(
                $settings,
                'validation'
            );
            $validation = $this->xmlGenerator->addXmlNode(
                $validation,
                'rule',
                $field['is_required'],
                ["name" => "required-entry", "xsi:type" => "boolean"]
            );
            $validation = $this->xmlGenerator->addXmlNode(
                $settings,
                'dataType',
                'text'
            );
            $i += 10;
        }
        $xmlData = $this->xmlGenerator->formatXml($listingXml->asXml());
        $this->helper->saveFile($componentXml, $xmlData);
    }
}
