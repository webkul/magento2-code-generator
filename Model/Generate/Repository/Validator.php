<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model\Generate\Repository;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'];
        $name = $data['name'];
        $path = $data['path']??null;
        $modelClass = $data['model-class']??null;
        $collectionClass = $data['collection-class']??null;
        $response = [];
        $response['type'] = $type;
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if (!$moduleData) {
                throw new \InvalidArgumentException(__("invalid module name"));
            }
            $response["module"] = $module;
            $response["name"] = $name;
        } else {
            throw new \InvalidArgumentException(__("module name not provided"));
        }
        
        if ($modelClass) {
            if (class_exists($modelClass)) {
                $response["model_class"] = $modelClass;
            } else {
                throw new \InvalidArgumentException(__("model class does not exist please check full name"));
            }
        } else {
            throw new \InvalidArgumentException(__("model class required"));
        }

        if ($collectionClass) {
            if (class_exists($collectionClass)) {
                $response["collection_class"] = $collectionClass;
            } else {
                throw new \InvalidArgumentException(__("collection class does not exist please check full name"));
            }
        } else {
            throw new \InvalidArgumentException(__("collection class required"));
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