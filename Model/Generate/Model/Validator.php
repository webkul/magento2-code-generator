<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model\Generate\Model;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'];
        $name = $data['name'];
        $table = $data['table']??null;
        $path = $data['path']??null;
        $response = [];
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if (!$moduleData) {
                throw new \InvalidArgumentException(__("invalid module name"));
            }
            if (!$table) {
                throw new \InvalidArgumentException(__("please provide table name for generating model"));
            }
            $response["module"] = $module;
            $response["table"] = $table;
            $response["name"] = $name;
        } else {
            throw new \InvalidArgumentException(__("module name not provided"));
        }

        switch (strtolower($type)) {

            case "model":
                if (!$name) {
                    
                    throw new \InvalidArgumentException(
                        __("enter model name that need to be generated")
                    );
                }
                $response["type"] = $type;
                break;

            case "controller":
                throw new \InvalidArgumentException(
                    __("enter controller name that need to be generated")
                );
                $response["type"] = $type;
                break;
            
            default:
                throw new \InvalidArgumentException(__("define type of code to be generated like model, controller, helper"));
        }
        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);

        if ($path) {

            $realPath = $modulePath.DIRECTORY_SEPARATOR.$path;
            
            if (!is_dir($realPath) || !file_exists($realPath)) {
                throw new \InvalidArgumentException(__("invalid module path given: ". $realPath));
            }
            $response["path"] = $realPath;
        } else {
            $response["path"] = $modulePath;
        }
        
        return $response;
    }
}