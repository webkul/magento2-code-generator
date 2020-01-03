<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate\Controller;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'];
        $name = $data['name'];
        $area = $data['area'] ?? null;
        $path = $data['path'] ?? null;
        $resource = $data['resource'] ?? null;
        $response = [];
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if (!$moduleData) {
                throw new \InvalidArgumentException(__("invalid module name"));
            }
            $response["module"] = $module;
        } else {
            throw new \InvalidArgumentException(__("module name not provided"));
        }
        
        if (!$area || !in_array($area, ['frontend', 'adminhtml'])) {
            throw new \InvalidArgumentException(__("invalid area type provided"));
        } else {
            $response['area'] = $area;
        }

        if ($name) {
            $response["name"] = $name;
        } else {
            throw new \InvalidArgumentException(__("name is required"));
        }

        $response['resource'] = $resource;

        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        if (isset($data['router'])) {
            $response['router'] = $data['router'];
        }
        
        $response["module_path"] = $modulePath;
        if ($area == 'frontend') {
            $response["path"] = $modulePath.DIRECTORY_SEPARATOR.'Controller';
        } else {
            $response["path"] = $modulePath.DIRECTORY_SEPARATOR.'Controller'.DIRECTORY_SEPARATOR.'Adminhtml';
        }
        if (!$path) {
            throw new \InvalidArgumentException(__("path is required"));
        } else {
            $pathParts = explode("_", $path);

            $response["path"] = $response["path"].DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $pathParts);
        }

        $response["type"] = $type;
        
        return $response;
    }
}
