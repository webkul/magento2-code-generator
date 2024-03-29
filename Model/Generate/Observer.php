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
 * Generate Observer
 */
class Observer implements GenerateInterface
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
        $data['observer-name'] = strtolower($moduleName.'_'.$data['name'].'_'.'observer');
        $data['observer-class'] = str_replace('_', '\\', $moduleName).'\\'.'Observer'.'\\'.$data['name'];
        
        $this->helper->createDirectory(
            $observerDirPath = $path.DIRECTORY_SEPARATOR.'Observer'
        );
        $this->helper->createDirectory(
            $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'
        );
        if ($data['area']!==null) {
            $this->helper->createDirectory(
                $etcDirPath = $path.DIRECTORY_SEPARATOR.'etc'.DIRECTORY_SEPARATOR.$data['area']
            );
        }
        $this->createObserver($observerDirPath, $data);
        $this->addEventsXmlData($etcDirPath, $data);
       
        return ['status' => 'success', 'message' => "Observer Generated Successfully"];
    }
    /**
     * Create Observer class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createObserver($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $observerFile = $this->helper->getTemplatesFiles('templates/observer/observer.php.dist');
        $observerFile = str_replace('%module_name%', $data['module'], $observerFile);
        $observerFile = str_replace('%observer_name%', $fileName, $observerFile);
        $observerFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1], $observerFile);
        
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $observerFile
        );
    }
    /**
     * Add events xml data
     *
     * @param string $etcDirPath
     * @param array $data
     * @return void
     */
    public function addEventsXmlData($etcDirPath, $data)
    {
        $eventName = $data['event-name'];
        $observerClass = $data['observer-class'];
        $observerName = $data['observer-name'];
        $replace = [
            "module_name" => $data['module']
        ];
        $eventsXmlFile = $this->helper->loadTemplateFile(
            $etcDirPath,
            'events.xml',
            'templates/events.xml.dist',
            $replace
        );
        $xmlObj = new Config($eventsXmlFile);
        $eventsXml = $xmlObj->getNode();
        $eventNode = $this->xmlGenerator->addXmlNode($eventsXml, 'event', '', ['name'=>$eventName]);
        $this->xmlGenerator->addXmlNode(
            $eventNode,
            'observer',
            '',
            ['name'=>$observerName, 'instance'=>$observerClass]
        );
        $xmlData = $this->xmlGenerator->formatXml($eventsXml->asXml());
        $this->helper->saveFile($eventsXmlFile, $xmlData);
    }
}
