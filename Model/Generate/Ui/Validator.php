<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastava
 */

namespace Webkul\CodeGenerator\Model\Generate\Ui;

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
        $modelClassName = $data['model_class_name'] ?? "";
        $tableName = $data['table'] ?? "";
        $columnsName = $data['columns_name'] ?? "";
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

        if ($modelClassName) {
            $response["model_class_name"] = $modelClassName;
        } else {
            throw new \InvalidArgumentException(__("Model class name is required"));
        }

        if ($tableName) {
            $response["table"] = $tableName;
        } else {
            throw new \InvalidArgumentException(__("Table name is required"));
        }

        if ($columnsName) {
            $response["columns_name"] = $columnsName;
        } else {
            throw new \InvalidArgumentException(__("Column name is required"));
        }
        
        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        $response["type"] = $type;
        
        return $response;
    }
}
