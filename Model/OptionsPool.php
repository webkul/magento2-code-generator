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

       //general options

        $options = [
            new InputArgument(
                'module',
                InputArgument::OPTIONAL,
                'provide your module name like Vendoe_ModuleName'
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

            //model specific

            new InputOption(
                'table',
                'ta',
                InputArgument::OPTIONAL,
                'table name for the model only required for model generation'
            ),

            //repository specific

            new InputOption(
                'model-class',
                'mc',
                InputArgument::OPTIONAL,
                'enter model class with namespace'
            ),

            new InputOption(
                'collection-class',
                'cc',
                InputArgument::OPTIONAL,
                'enter collection class with namespace'
            ),
            
          // shipping method specific
          
            new InputOption(
                'shipping-code',
                'shipping-code',
                InputArgument::OPTIONAL,
                'enter shipping method code.'
            ),
          
            //controller specific

            new InputOption(
                'area',
                'ar',
                InputArgument::OPTIONAL,
                'enter area frontend or adminhtml'
            ),

            //observr specific
            new InputOption(
                'event',
                'event',
                InputArgument::OPTIONAL,
                'enter event name'
            )

        ];

        return $options;
    }

}