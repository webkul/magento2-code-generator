<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Mahesh Singh
 */

namespace Webkul\CodeGenerator\Model\Generate\NewModule;

use Magento\Framework\Exception\LocalizedException;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    private $validationRule = '/^[a-zA-Z]+[a-zA-Z0-9._]+$/';

    public function validate($data)
    {
        $type = $data['type'];
        $module = $data['module'];
        $response = [];
        if (!$type) {
            throw new \InvalidArgumentException(__('define type of code to be generated "new-module"'));
        }
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if ($moduleData) {
                throw new LocalizedException(
                    __(
                        '%1 Module already exists.',
                        $module
                    )
                );
            }
            $response["module"] = $module;
            $response["type"] = $type;
            
        } else {
            throw new \InvalidArgumentException(__("module name not provided"));
        }
        $moduleNameSplit = explode('_', $module);
        if (!isset($moduleNameSplit[1])) {
            throw new \RuntimeException(
                __('Incorrect module name "%1", correct name ex: Webkul_Test', $module)
            );
        }
        
        foreach ($moduleNameSplit as $part) {
            if (!preg_match($this->validationRule, $part)) {
                throw new \RuntimeException(
                    __('Module vendor or name must be alphanumeric.')
                );
            }
        }
        
        return $response;
    }
}
