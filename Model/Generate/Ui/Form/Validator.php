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
        $formField = $data['form_field'] ?? "";
        $fieldset = $data['fieldset_name'] ?? "";
        $fieldsetLabel = $data['fieldset_label'] ?? "";
        $formUrl = $data['submit_url'] ?? "";
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

        if ($fieldset) {
            $response["fieldset_name"] = $fieldset;
        } else {
            throw new \InvalidArgumentException(__("Field Set is required"));
        }

        if ($fieldsetLabel) {
            $response["fieldset_label"] = $fieldsetLabel;
        } else {
            throw new \InvalidArgumentException(__("Field Set Label is required"));
        }

        if ($formField) {
            $response["form_field"] = $formField;
        } else {
            throw new \InvalidArgumentException(__("Form Field is required"));
        }

        if ($formUrl) {
            $response["submit_url"] = $formUrl;
        } else {
            throw new \InvalidArgumentException(__("Form Url is required"));
        }

        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;
        $response["type"] = $type;
        
        return $response;
    }
}
