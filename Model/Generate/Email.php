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
 * Class Email
 */
class Email implements GenerateInterface
{
    protected $helper;
    
    protected $xmlGeneratorFactory;

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

        Helper::createDirectory(
            $emailDirPath = $path.DIRECTORY_SEPARATOR.'view/frontend/email'
        );
        
        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        
        $this->createEmailTemplate($emailDirPath, $data);
        $this->addEmailXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Email Template Generated Successfully"];
    }

    /**
     * create email template
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
     * add email_templates.xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addEmailXmlData($etcDirPath, $data)
    {
        $emailXmlFile = $this->helper->loadTemplateFile($etcDirPath, 'email_templates.xml', 'templates/email/email_templates.xml.dist');
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
