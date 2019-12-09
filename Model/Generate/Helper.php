<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate;

use Webkul\CodeGenerator\Model\Helper as CodeHelper;
use Webkul\CodeGenerator\Api\GenerateInterface;

/**
 * Class Helper
 */
class Helper implements GenerateInterface
{

    protected $helper;

    public function __construct(
        CodeHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute($data)
    {
        $modelName = $data['name'];
        $path = $data['path'];
        
        CodeHelper::createDirectory(
            $helperDirPath = $path.DIRECTORY_SEPARATOR.'Helper'
        );
       
        $this->createHelper($helperDirPath, $data);
       
        return ['status' => 'success', 'message' => "Helper Class generated successfully"];
    }

    /**
     * create helper class
     *
     * @param string $dir
     * @param array $data
     * @return void
     */
    public function createHelper($dir, $data)
    {
        $fileName = ucfirst($data['name']);
        $nameSpace = $data['module'];
        $nameArray = explode("_", $nameSpace);
        $helperFile = $this->helper->getTemplatesFiles('templates/helper/helper.php.dist');
        $helperFile = str_replace('%module_name%', $data['module'], $helperFile);
        $helperFile = str_replace('%helper_name%', $fileName, $helperFile);
        $helperFile = str_replace('%namespace%', $nameArray[0].'\\'.$nameArray[1], $helperFile);
        $this->helper->saveFile(
            $dir.DIRECTORY_SEPARATOR.$fileName.'.php',
            $helperFile
        );
    }
}
