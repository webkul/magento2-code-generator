<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model;

/**
 * Class GeneratorPool
 */
class GeneratorPool
{

    protected $generators = [];

    public function __construct(
        $generators = []
    ) {
        $this->generators = $generators;
    }

    /**
     * get generator class
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
