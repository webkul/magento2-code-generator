<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Api;

/**
 * Interface GenerateInterface
 */
interface GenerateInterface
{

    /**
     * Generate code
     *
     * @param array $data
     * @return boolean
     */
    public function execute($data);
}
