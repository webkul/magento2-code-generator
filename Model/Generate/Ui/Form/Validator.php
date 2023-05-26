<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate\Ui\Form;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    /**
     * Validate Command Params
     *
     * @param array $data
     * @return array
     */
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'] ?? "";
        $name = $data['name'] ?? "";
        $providerName = $data['provider_name'] ?? "";
        $model = $data['model_class_name'] ?? "";
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

        if ($name) {
            $response["name"] = $name;
        } else {
            throw new \InvalidArgumentException(__("name is required"));
        }

        if ($providerName) {
            $response["provider_name"] = $providerName;
        } else {
            throw new \InvalidArgumentException(__("Provider name is required"));
        }

        if ($model) {
            $response["model_class_name"] = $model;
        } else {
            throw new \InvalidArgumentException(__("Model is required"));
        }

        // if ($columnsName) {
        //     $response["columns_name"] = $columnsName;
        // } else {
        //     throw new \InvalidArgumentException(__("Column name is required"));
        // }
        
        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        $response["type"] = $type;
        
        return $response;
    }
}
