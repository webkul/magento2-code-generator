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
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\ParameterGenerator;

/**
 * Generate UiListing
 */
class UiListing implements GenerateInterface
{
    /**
     * @var CodeHelper
     */
    protected $helper;

    /**
     * @var object
     */
    protected $docblock;

    /**
     * __construct function
     *
     * @param CodeHelper $helper
     */
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
        $path = $data['path'];
        $this->docblock = $this->helper->getHeadDocBlock($data['module']);
        $this->helper->createDirectory(
            $lsitingDir = $path.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'adminhtml'
                .DIRECTORY_SEPARATOR.'ui_component'
        );
        $this->helper->createDirectory(
            $lsitingDir = $path.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'adminhtml'
                .DIRECTORY_SEPARATOR.'ui_component'
        );
        $this->generateUiListing($lsitingDir, $data);
       
        return ['status' => 'success', 'message' => "unit test cases successfully generated"];
    }
}
