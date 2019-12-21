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
 * Class Rewrite
 */
class Rewrite implements GenerateInterface
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
        $path = $data['path'];

        Helper::createDirectory(
            $rewriteDirPath = $path.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $data["rewrite-path"])
        );
        
        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );

        $this->createRewrite($rewriteDirPath, $data);
        $this->addDiXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Rewrite Class Generated Successfully"];
    }

    /**
     * create Rewrite class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createRewrite($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $rewriteFile = $this->helper->getTemplatesFiles('templates/rewrite/rewrite.php.dist');
        $rewriteFile = str_replace('%module_name%', $data['module'], $rewriteFile);
        $rewriteFile = str_replace('%name%', $fileName, $rewriteFile);
        $rewriteFile = str_replace('%rewrite%', $data['rewrite'], $rewriteFile);
        $rewriteFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1].'\\'.str_replace('_', '\\', $data['rewrite-path']), $rewriteFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $rewriteFile
        );
    }

    /**
     * add di xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addDiXmlData($etcDirPath, $data)
    {
        $preferenceType = str_replace('_', '\\', $data['module'].'_'.$data['rewrite-path'].'_'.ucfirst($data['name']));
        $diXmlFile = $this->helper->getDiXmlFile($etcDirPath);
        $xmlObj = new Config($diXmlFile);
        $diXml = $xmlObj->getNode();
        $typeNode = $this->xmlGenerator->addXmlNode(
                                            $diXml, 
                                            'preference', 
                                            '', 
                                            ['for'=>$data['rewrite'], 'type'=>$preferenceType]
                                        );
        $xmlData = $this->xmlGenerator->formatXml($diXml->asXml());
        $this->helper->saveFile($diXmlFile, $xmlData);
    }
}
