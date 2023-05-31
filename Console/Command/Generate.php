<?php
/**
 * Webkul Software.
 *
 * @package   Webkul_CodeGenerator
 * @author    Ashutosh Srivastva
 */

namespace Webkul\CodeGenerator\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Magento\Framework\Validation\ValidationException;

/**
 * Generate GreetingCommand
 */
class Generate extends Command
{
    /**
     * @var [type]
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $validators;

    /**
     * @var \Webkul\CodeGenerator\Model\OptionsPool
     */
    protected $optionsPool;

    /**
     * @var array
     */
    protected $formField = [];

    /**
     * __construct function
     *
     * @param array $validators
     */
    public function __construct(
        $validators = []
    ) {
        $this->validators = $validators;
        $this->optionsPool = \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Webkul\CodeGenerator\Model\OptionsPool::class);
        $state = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
        $state->setAreaCode("adminhtml");
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        
        $options = $this->optionsPool->getOptions();

        $this->setName('generate:code')
            ->setDescription('Generate Module Code')
            ->setDefinition(
                $options
            );
        parent::configure();
    }

    /**
     * Creation admin user in interaction mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $data = $input->getOptions();

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        switch ($data['type']) {
            case "new-module":
                $this->generateNewModule($input, $output, $questionHelper);
                break;
            case "ui_component_listing":
                $this->generateUiComponentListing($input, $output, $questionHelper);
                break;
            case "create-view":
                $this->generateView($input, $output, $questionHelper);
                break;
            case "controller":
                $this->generateController($input, $output, $questionHelper);
                break;
            case "model":
                $this->generateModel($input, $output, $questionHelper);
                break;
            case "repository":
                $this->generateRepository($input, $output, $questionHelper);
                break;
            case "helper":
                $this->generateHelper($input, $output, $questionHelper);
                break;
            case "plugin":
                $this->generatePlugin($input, $output, $questionHelper);
                break;
            case "observer":
                $this->generateObserver($input, $output, $questionHelper);
                break;
            case "cron":
                $this->generateCron($input, $output, $questionHelper);
                break;
            case "logger":
                $this->generateLogger($input, $output, $questionHelper);
                break;
            case "command":
                $this->generateCommand($input, $output, $questionHelper);
                break;
            case "rewrite":
                $this->generateOverrideClass($input, $output, $questionHelper);
                break;
            case "email":
                $this->generateEmailTemplate($input, $output, $questionHelper);
                break;
            case "payment":
                $this->generatePaymentMethod($input, $output, $questionHelper);
                break;
            case "shipping":
                $this->generateShippingMethod($input, $output, $questionHelper);
                break;
            case "unit-test":
                $this->generateUnitTest($input, $output, $questionHelper);
                break;
            case "ui_component_form":
                $this->generateUiComponentForm($input, $output, $questionHelper);
                break;
            default:
                throw new ValidationException(__('Invalid type.'));
        }
    }

    /**
     * Generate New Module
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateNewModule($input, $output, $questionHelper)
    {
        if (!$input->getOption('vendor_name')) {
            $question = new Question('<question>Enter Vendor Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "vendor_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('module_name')) {
            $question = new Question('<question>Enter Module Name:</question> ', 'Veno');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "module_name",
                $questionHelper->ask($input, $output, $question)
            );

            $input->setArgument(
                'module',
                $input->getOption('vendor_name').'_'.$input->getOption('module_name')
            );
        }
    }

    /**
     * Generate Ui Component
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateUiComponentListing($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Ui Component Listing File Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('columns_name')) {
            $question = new Question('<question>Enter Ui Component Columns Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "columns_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('model_class_name')) {
            $question = new Question('<question>Enter Model Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "model_class_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('table')) {
            $question = new Question('<question>Enter Table Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "table",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Create View
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateView($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Layout Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('area')) {
            $question = new Question('<question>Enter Area Scope:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "area",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('block-class')) {
            $question = new Question('<question>Enter Block Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "block-class",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('template')) {
            $question = new Question('<question>Enter Template Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "template",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('layout-type')) {
            $question = new Question('<question>Enter Layout Type:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "layout-type",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Controller
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateController($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Controller Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('area')) {
            $question = new Question('<question>Enter Area Scope:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "area",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('path')) {
            $question = new Question('<question>Enter Controller Relative Path:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "path",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('router')) {
            $question = new Question('<question>Enter Routes Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "router",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Model
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateModel($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Model Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('table')) {
            $question = new Question('<question>Enter Table Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "table",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Repository
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateRepository($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Repository Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('model-class')) {
            $question = new Question('<question>Enter Model Class Name With Namespace:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "model-class",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('collection-class')) {
            $question = new Question('<question>Enter Collection Class Name With Namespace:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "collection-class",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Helper
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateHelper($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Helper Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Plugin
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generatePlugin($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Plugin Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('plugin')) {
            $question = new Question('<question>Enter Plugin Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "plugin",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('area')) {
            $question = new Question('<question>Enter Area Scope:</question> ', '');
            $input->setOption(
                "area",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Observer
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateObserver($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Observer Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('event')) {
            $question = new Question('<question>Enter Event Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "event",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('area')) {
            $question = new Question('<question>Enter Area Scope:</question> ', '');
            $input->setOption(
                "area",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }
    
    /**
     * Generate Cron
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateCron($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Cron Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('schedule')) {
            $question = new Question('<question>Enter Cron Schedule:</question> ', '0 1 * * *');
            $input->setOption(
                "schedule",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Logger
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateLogger($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Log Name:</question> ', $input->getOption('module_name'));
            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateCommand($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Command Class Name:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('command')) {
            $question = new Question('<question>Enter Command Name:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "command",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Overridden Class
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateOverrideClass($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Name:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('rewrite')) {
            $question = new Question('<question>Enter Overridden Class:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "rewrite",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('path')) {
            $question = new Question('<question>Enter Relative Path:</question> ', 'Rewrite');
            $input->setOption(
                "path",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Email Template
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateEmailTemplate($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Email Template Name:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('id')) {
            $question = new Question('<question>Enter Email Template Id:</question> ');
            $input->setOption(
                "id",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Payment Method
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generatePaymentMethod($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('payment-code')) {
            $question = new Question('<question>Enter Payment Code:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "payment-code",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Payment Name:</question> ', 'Custom Payment');
            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }
    
    /**
     * Generate Shipping Method
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateShippingMethod($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('shipping-code')) {
            $question = new Question('<question>Enter Shipping Code:</question> ');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "shipping-code",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Shipping Name:</question> ', 'Custom Shipping');
            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Generate Unit Test
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateUnitTest($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);
    }

    /**
     * Generate Ui Component Form
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function generateUiComponentForm($input, $output, $questionHelper)
    {
        $this->setInputArgument($input, $output, $questionHelper);

        if (!$input->getOption('name')) {
            $question = new Question('<question>Enter Ui Component Form File Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('provider_name')) {
            $question = new Question('<question>Enter Data Provider Name:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "provider_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('model_class_name')) {
            $question = new Question('<question>Enter Model Class Name:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "model_class_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('fieldset_name')) {
            $question = new Question('<question>Enter Form Fieldset Name:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "fieldset_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('fieldset_label')) {
            $question = new Question('<question>Enter Form Fieldset Label:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "fieldset_label",
                $questionHelper->ask($input, $output, $question)
            );
        }

        $this->addFormField($input, $output, $questionHelper);
    }

    /**
     * Generate Ui Component Form
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    public function addFormField($input, $output, $questionHelper)
    {
        if (!$input->getOption('field_name')) {
            $question = new Question('<question>Enter Field Name:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "field_name",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('field_type')) {
            $question = new Question('<question>Enter Field Type:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "field_type",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('field_label')) {
            $question = new Question('<question>Enter Field Label:</question> ', '');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            $input->setOption(
                "field_label",
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption('enter_new_field')) {
            $question = new Question('<question>Enter New Field (yes/no):</question> ', 'no');
            $this->addNotEmptyValidator($question);
            $this->classNameValidator($question);
            echo $questionHelper->ask($input, $output, $question);die;
            $input->setOption(
                "enter_new_field",
                $questionHelper->ask($input, $output, $question)
            );
            if ($input->getOption('enter_new_field') == "yes") {
                $this->addFormField($input, $output, $questionHelper);
            }
        }
    }

    /**
     * Set Module Name in input argument
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\QuestionHelper $questionHelper
     * @return void
     */
    protected function setInputArgument($input, $output, $questionHelper)
    {
        if (!$input->getOption('module_name')) {
            $question = new Question('<question>Enter Module Name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                "module_name",
                $questionHelper->ask($input, $output, $question)
            );

            $input->setArgument(
                'module',
                $input->getOption('module_name')
            );
        }
    }
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = null;
        $data = $input->getOptions();
        $data['module'] = $input->getArgument('module');
        if (isset($this->validators[$data['type']])) {
            $data = $this->validators[$data['type']]->validate($data);
            if ($this->generate($data, $output)) {
                return 0;
            }
        } else {
            throw new \InvalidArgumentException(__("invalid type"));
        }
        exit;
    }

    /**
     * Generate code
     *
     * @param array $data
     * @param Output $output
     * @return bool
     */
    private function generate($data, $output)
    {
        $output->writeln("<info>====> Code Generation started \n". json_encode($data).'</info>');
        $generatorPool = \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Webkul\CodeGenerator\Model\GeneratorPool::class);

        $generator = $generatorPool->get(strtolower($data['type']));
        try {
            $response = $generator->execute($data);
            if ($response['status'] == 'failed') {
                $output->writeln("<error>====> ".$response['message'].'</error>');
            } else {
                $output->writeln("<info>====> ".$response['message'].'</info>');
                return true;
            }
        } catch (\Exception $e) {
            $output->writeln("<error>====> ".$e->getMessage().'</error>');
        }
        return false;
    }

    /**
     * Add not empty validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addNotEmptyValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new ValidationException(__('The value cannot be empty'));
            }

            return $value;
        });
    }

    /**
     * Add class name validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function classNameValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            // if (preg_match("/^[a-zA-Z'-]+$/", $value)) {
            //     throw new ValidationException(__('Enter valid class name.'));
            // }

            return $value;
        });
    }
}
