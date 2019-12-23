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
 * Class Cron
 */
class Cron implements GenerateInterface
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
        $data['cron-name'] = strtolower($moduleName.'-'.$data['name'].'-'.'cron');
        $data['cron-class'] = str_replace('_', '\\', $moduleName).'\\'.'Cron'.'\\'.$data['name'];
        
        Helper::createDirectory(
            $cronDirPath = $path.DIRECTORY_SEPARATOR.'Cron'
        );
        
        Helper::createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        
        $this->createCron($cronDirPath, $data);
        $this->addCrontabXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Cron Class Generated Successfully"];
    }

    /**
     * create cron class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createCron($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $cronFile = $this->helper->getTemplatesFiles('templates/cron/cron.php.dist');
        $cronFile = str_replace('%module_name%', $data['module'], $cronFile);
        $cronFile = str_replace('%cron_name%', $fileName, $cronFile);
        $cronFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1], $cronFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $cronFile
        );
    }

    /**
     * add crontab.xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addCrontabXmlData($etcDirPath, $data)
    {
        $schedule = $data['schedule'];
        $cronName = $data['cron-name'];
        $cronClass = $data['cron-class'];
        $crontabXmlFile = $this->helper->loadTemplateFile($etcDirPath, 'crontab.xml', 'templates/crontab.xml.dist');
        $xmlObj = new Config($crontabXmlFile);
        $configXml = $xmlObj->getNode();
        if (!$configXml->group) {
            throw new \RuntimeException(
                __('Incorrect crontab.xml schema found')
            );
        }
        $jobNode = $this->xmlGenerator->addXmlNode($configXml->group, 'job', '', ['instance'=>$cronClass, 'method'=>'execute', 'name'=>$cronName]);
        $this->xmlGenerator->addXmlNode($jobNode, 'schedule', $schedule);
        $xmlData = $this->xmlGenerator->formatXml($configXml->asXml());
        $this->helper->saveFile($crontabXmlFile, $xmlData);
    }
}
