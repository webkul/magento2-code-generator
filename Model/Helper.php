<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model;

use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\DocBlock\Tag;

class Helper
{

    public function saveFile($path, $content)
    {
        file_put_contents(
            $path,
            $content
        );
    }

    public function getHeadDocBlock($moduleName)
    {
        return DocBlockGenerator::fromArray([
            'shortDescription' => 'Webkul Software.',
            'tags'             => [
                new Tag\GenericTag('category', 'Webkul'),
                new Tag\GenericTag('package', $moduleName),
                new Tag\GenericTag('author', 'Webkul'),
                new Tag\GenericTag('copyright', 'Copyright (c) Webkul Software Private Limited (https://webkul.com)'),
                new Tag\LicenseTag('https://store.webkul.com/license.html', '')
               
            ],
        ]);
    }

    public static function createDirectory($dirPath, $permission = 0777)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, $permission, true);
        }
    }

    public function getTemplatesFiles($template)
    {
        return file_get_contents(dirname( dirname(__FILE__) ) . DIRECTORY_SEPARATOR. $template);
    }

}
