<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sanjay Chouhan
 */

namespace Webkul\CodeGenerator\Model\Generate\Plugin;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    /**
     * Validate command params
     *
     * @param array $data
     * @return array
     */
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'];
        $name = $data['name'];

        $response = [];
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if (!$moduleData) {
                throw new \InvalidArgumentException(__("Invalid module name"));
            }
            $response["module"] = $module;
        } else {
            throw new \InvalidArgumentException(__("Module name not provided"));
        }

        if ($name) {
            $response["name"] = $name;
        } else {
            throw new \InvalidArgumentException(__("Name is required"));
        }

        if (isset($data['plugin-type']) && $data['plugin-type']) {
            $pluginType = ltrim($data['plugin-type'], '\\');
            if (class_exists('\\'.$pluginType)) {
                $response["plugin-type"] = $pluginType;
            } else {
                throw new \InvalidArgumentException(__("Plugin Type class does not exist"));
            }
        } else {
            throw new \InvalidArgumentException(__("Plugin Type is required"));
        }

        $dir = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        if (isset($data['area']) && $data['area']) {
            $response["area"] = $data['area'];
        } else {
            $response["area"] = null;
        }
        $response["type"] = $type;
        
        return $response;
    }
}