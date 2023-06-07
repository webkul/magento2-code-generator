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
        $button = '"'.$nameArray[0].'\\'.$nameArray[1].'\\'."Block".'\\'."Adminhtml".'\\'."General".'\\'."Edit".'\\';
        $replace = [
            "module_name" => $data['module'],
            "form_name" => $data['name'],
            "form_label" => $nameArray[0].' '.$nameArray[1],
            "save_botton" => $button."SaveButton".'"',
            "back_button" => $button."BackButton".'"',
            "submit_url" => $data['submit_url'],
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
                [
                    "name" => strtolower($field['field_name']),
                    "formElement" => $field['field_type'],
                    "sortOrder" => $i
                ]
            );
            switch (strtolower($field['field_type'])) {
                case 'input':
                    $this->addInputField($xmlField, $field);
                    break;
                case 'select':
                    $this->addSelectOrMultiselectField($xmlField, $field, $data);
                    break;
                case 'multiselect':
                    $this->addSelectOrMultiselectField($xmlField, $field, $data);
                    break;
                case 'imageuploader':
                    $this->addImageField($xmlField, $field, $data);
                    break;
            }
            $i += 10;
        }
        $xmlData = $this->xmlGenerator->formatXml($listingXml->asXml());
        $this->helper->saveFile($componentXml, $xmlData);
    }

    /**
     * Add Input Field in form
     *
     * @param \Magento\Framework\Simplexml\Element $xmlField
     * @param array $field
     * @return void
     */
    public function addInputField($xmlField, $field)
    {   
        /* Add Field Setting */
        $settings = $this->xmlGenerator->addXmlNode(
            $xmlField,
            'settings'
        );
        $validation = $this->xmlGenerator->addXmlNode(
            $settings,
            'validation'
        );
        $this->xmlGenerator->addXmlNode(
            $validation,
            'rule',
            $field['is_required'],
            ["name" => "required-entry", "xsi:type" => "boolean"]
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'dataType',
            'text'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'label',
            $field['field_label']
        );
    }

    /**
     * Add Select Field in form
     *
     * @param \Magento\Framework\Simplexml\Element $xmlField
     * @param array $field
     * @param array $data
     * @return void
     */
    public function addSelectOrMultiselectField($xmlField, $field, $data)
    {   
        /* Add Field Setting */
        $settings = $this->xmlGenerator->addXmlNode(
            $xmlField,
            'settings'
        );
        $validation = $this->xmlGenerator->addXmlNode(
            $settings,
            'validation'
        );
        $this->xmlGenerator->addXmlNode(
            $validation,
            'rule',
            $field['is_required'],
            ["name" => "required-entry", "xsi:type" => "boolean"]
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'dataType',
            'int'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'label',
            $field['field_label']
        );

        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $nameSpace = $nameArray[0].'\\'.$nameArray[1].'\\'."Model".'\\'."Config".'\\'."Source";

        $formElements = $this->xmlGenerator->addXmlNode(
            $xmlField,
            'formElements'
        );
        $select = $this->xmlGenerator->addXmlNode(
            $formElements,
            strtolower($field['field_type'])
        );
        $settings = $this->xmlGenerator->addXmlNode(
            $select,
            'settings'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'options',
            '',
            ["class" => $nameSpace.'\\'.'Options']
        );
        $option = $this->helper->getTemplatesFiles('templates/ui_component/option.php.dist');
        $option = str_replace('%module_name%', $data['module'], $option);
        $option = str_replace('%namespace%', $nameSpace, $option);

        $path = $data['path'];
        $this->helper->createDirectory(
            $oprionDir = $path.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Config'
                .DIRECTORY_SEPARATOR.'Source'
        );
        $this->helper->saveFile(
            $oprionDir.DIRECTORY_SEPARATOR.'Options.php',
            $option
        );
    }

    /**
     * Add Image Field
     *
     * @param \Magento\Framework\Simplexml\Element $xmlField
     * @param array $field
     * @param array $data
     * @return void
     */
    public function addImageField($xmlField, $field, $data)
    {
        $settings = $this->xmlGenerator->addXmlNode(
            $xmlField,
            'settings'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'label',
            $field['field_label']
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'componentType',
            'imageUploader'
        );
        $formElements = $this->xmlGenerator->addXmlNode(
            $xmlField,
            'formElements'
        );
        $select = $this->xmlGenerator->addXmlNode(
            $formElements,
            'imageUploader'
        );
        $settings = $this->xmlGenerator->addXmlNode(
            $select,
            'settings'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'allowedExtensions',
            'jpg jpeg gif png'
        );
        $this->xmlGenerator->addXmlNode(
            $settings,
            'maxFileSize',
            '2097152'
        );

        $imageTemplate = $this->helper->getTemplatesFiles('templates/ui_component/image-preview.html.dist');
        $imageTemplate = str_replace('%module_name%', $data['module'], $imageTemplate);
        $path = $data['path'];
        $this->helper->createDirectory(
            $imageTemplateDir = $path.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'adminhtml'
                .DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'template'
        );
        $this->helper->saveFile(
            $imageTemplateDir.DIRECTORY_SEPARATOR.'image-preview.html',
            $imageTemplate
        );

        $this->xmlGenerator->addXmlNode(
            $settings,
            'previewTmpl',
            $data['module'].'/'.'image-preview.html'
        );
        $uploaderConfig = $this->xmlGenerator->addXmlNode(
            $settings,
            'uploaderConfig'
        );
        $this->xmlGenerator->addXmlNode(
            $uploaderConfig,
            'param',
            $field['image_upload_url'],
            ["xsi:type" => "string", "name" => "url"]
        );
    }
}
