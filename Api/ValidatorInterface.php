<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Webkul Software Pvt Ltd
 */

namespace Webkul\CodeGenerator\Api;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{

    /**
     * Generate code
     *
     * @param array $data
     * @return boolean
     */
    public function validate($data);
}
