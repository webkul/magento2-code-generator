<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Api;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{

    /**
     * generate code
     *
     * @param [] $data
     * @return boolean
     */
    public function validate($data);

}