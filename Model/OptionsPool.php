<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Model;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class OptionsPool {

    public function getOptions()
    {

        $options = [
            new InputArgument(
                'module',
                InputArgument::OPTIONAL,
                'provide your module name like Vendoe_ModuleName'
            ),
            new InputOption(
                'table',
                'ta',
                InputArgument::OPTIONAL,
                'table name for the model only required for model generation'
            ),
            new InputOption(
                'type',
                't',
                InputArgument::OPTIONAL,
                'define type of code to be generated like model, controller, helper'
            ),
            new InputOption(
                'path',
                'p',
                InputArgument::OPTIONAL,
                'provide relative path to your module folder to generate code'
            ),
            new InputOption(
                'name',
                'name',
                InputArgument::OPTIONAL,
                'enter model name or class name that need to be generated'
            ),
        ];

        return $options;
    }

}