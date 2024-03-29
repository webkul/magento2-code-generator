<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sanjay Chouhan
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Model\Helper;
use Webkul\CodeGenerator\Api\GenerateInterface;
use Webkul\CodeGenerator\Model\XmlGeneratorFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Simplexml\Config;
use Magento\Framework\Simplexml\Element;

/**
 * Generate Email Template
 */
class Email implements GenerateInterface
{
    /**
     * @var Helper
     */
    protected $helper;
    
    /**
     * @var XmlGeneratorFactory
     */
    protected $xmlGenerator;

    /**
     * Constructor
     *
     * @param XmlGeneratorFactory $xmlGeneratorFactory
     * @param Helper $helper
     */
    public function __construct(
        XmlGeneratorFactory $xmlGeneratorFactory,
        Helper $helper
    ) {
        $this->helper = $helper;
        $this->xmlGenerator = $xmlGeneratorFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $moduleName = $data['module'];
        $path = $data['path'];

        $this->helper->createDirectory(
            $emailDirPath = $path.DIRECTORY_SEPARATOR.'view/frontend/email'
        );
        
        $this->helper->createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        
        $this->createEmailTemplate($emailDirPath, $data);
        $this->addEmailXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Email Template Generated Successfully"];
    }

    /**
     * Create email template
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createEmailTemplate($dir, $data)
    {
        $emailFile = $this->helper->getTemplatesFiles('templates/email/email.html.dist');
        $emailFile = str_replace('%module_name%', $data['module'], $emailFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$data['template'].'.html',
            $emailFile
        );
    }

    /**
     * Add email_templates.xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addEmailXmlData($etcDirPath, $data)
    {
        $replace = [
            "module_name" => $data['module'],
        ];
        $emailXmlFile = $this->helper->loadTemplateFile(
            $etcDirPath,
            'email_templates.xml',
            'templates/email/email_templates.xml.dist',
            $replace
        );
        $xmlObj = new Config($emailXmlFile);
        $configXml = $xmlObj->getNode();
        $this->xmlGenerator->addXmlNode(
            $configXml,
            'template',
            '',
            [
                'id'=>$data['id'],
                'label'=>$data['name'],
                'file'=>$data['template'].'.html',
                'type'=>'html',
                'area'=>'frontend',
                'module'=>$data['module']
            ]
        );
        $xmlData = $this->xmlGenerator->formatXml($configXml->asXml());
        $this->helper->saveFile($emailXmlFile, $xmlData);
    }
}
