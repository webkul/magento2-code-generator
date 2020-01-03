<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sanjay Chouhan
 */

namespace Webkul\CodeGenerator\Model\Generate\Email;

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
            throw new \InvalidArgumentException(__("name is required"));
        }

        if (isset($data['template']) && $data['template']) {
            $response["template"] = $data['template'];
        } else {
            $response["template"] = $this->getSanitized($name);
        }

        if (isset($data['id']) && $data['id']) {
            $response["id"] = $data['id'];
        } else {
            $moduleName = explode('_', $module)[1];
            $response["id"] = $this->getSanitized($moduleName.'_email_'.$name);
        }

        $dir = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        $response["type"] = $type;
        
        return $response;
    }

    private function getSanitized($string)
    {
        return strtolower(preg_replace( '/[^a-z0-9]/i', '_', $string));
    }
}