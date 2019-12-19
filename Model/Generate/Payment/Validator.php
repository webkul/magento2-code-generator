<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Sagar Bathla
 */

namespace Webkul\CodeGenerator\Model\Generate\Payment;

class Validator implements \Webkul\CodeGenerator\Api\ValidatorInterface
{
    public function validate($data)
    {
        $module = $data['module'];
        $type = $data['type'];
        $code = $data['payment-code'];
        $name = $data['name']??'Custom Payment';
        $path = $data['path']??null;
        $response = [];
        if ($module) {
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\ModuleListInterface::class);
            $moduleData = $moduleManager->getOne($module);
            if (!$moduleData) {
                throw new \InvalidArgumentException(__("invalid module name"));
            }if (!$code) {
                throw new \InvalidArgumentException(__("please provide payment method code."));
            }
            if ($this->validatePaymentMethod($code)) {
                throw new \InvalidArgumentException(
                    __('payment method for "%1" code already exists.', strtolower($code))
                );
            }
            $response["module"] = $module;
            $response["code"] = $code;
            $response["name"] = $name;
            $response["type"] = $type;
        } else {
            throw new \InvalidArgumentException(__("module name not provided"));
        }
        
        $dir = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Dir::class);

        $modulePath = $dir->getDir($module);

        if ($path) {

            $realPath = $modulePath.DIRECTORY_SEPARATOR.$path;
            
            // @codingStandardsIgnoreStart
            if (!is_dir($realPath) || !file_exists($realPath)) {
                throw new \InvalidArgumentException(__("invalid module path given: ". $realPath));
            }
            // @codingStandardsIgnoreEnd
            $response["path"] = $realPath;
        } else {
            $response["path"] = $modulePath;
        }
        
        return $response;
    }

    /**
     * Check if payment method already exists
     *
     * @param string $name
     * @return null
     * @throws \InvalidArgumentException
     */
    private function validatePaymentMethod($code)
    {
        $filteredCode = $this->filterCode($code);
        if (!$filteredCode || $filteredCode == '') {
            throw new \InvalidArgumentException(
                __('invalid payment method code "%1" given.', $code)
            );
        }
        $inputCode = str_replace(" ", "_", strtolower($filteredCode));
        $paymentMethods = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Payment\Model\Config\Source\Allmethods::class);

        $methods = $paymentMethods->toOptionArray();
        $methodCodes = array_keys($methods);
        
        if (in_array($inputCode, $methodCodes)) {
            throw new \InvalidArgumentException(
                __('payment method for "%1" code already exists.', strtolower($code))
            );
        }
    }

    /**
     * Filter payment code
     *
     * @param string $code
     * @return string
     */
    private function filterCode($code)
    {
        return preg_replace('/[^a-zA-Z0-9_]/s', '', $code);
    }
}
