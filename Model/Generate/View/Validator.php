<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Mahesh Singh
 */
namespace Webkul\CodeGenerator\Model\Generate\View;

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
        $response = [];
        $module = $data['module'];
        $type = $data['type'];
        $name = $data['name'];
        $area = $data['area'];
        $response['phtml'] = $data['template']??'content.phtml';
        $response['block'] = $data['block-class']??'Main';
        $response['layout'] = $data['layout-type']??'1column';
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
        if (strpos($response['phtml'], '.phtml') === false) {
            $response['phtml'] = $response['phtml'].'.phtml';
        }

        $dir = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\Module\Dir::class);
        $modulePath = $dir->getDir($module);
        $response["path"] = $modulePath;

        if (isset($data['area']) && $data['area'] && in_array($data['area'], ['frontend', 'adminhtml'])) {
            $response["area"] = $data['area'];
        } else {
            throw new \InvalidArgumentException(__("Area is required or invalid"));
        }
        $response["type"] = $type;

        return $response;
    }
}