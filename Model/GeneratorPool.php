<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Webkul Software Pvt Ltd
 */

namespace Webkul\CodeGenerator\Model;

/**
 * Generate GeneratorPool
 */
class GeneratorPool
{
    /**
     * @var array
     */
    protected $generators = [];

    /**
     * __construct function
     *
     * @param array $generators
     */
    public function __construct(
        $generators = []
    ) {
        $this->generators = $generators;
    }

    /**
     * Get generator class
     *
     * @param string $key
     * @return Webkul\CodeGenerator\Api\GenerateInterface
     */
    public function get($key)
    {
        if (isset($this->generators[$key])) {
            return $this->generators[$key];
        }

        throw new \Magento\Framework\Exception\LocalizedException(__("invalid generator"));
    }
}
