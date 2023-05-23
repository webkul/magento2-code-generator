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
        if ($data['type'] == "db_schema") {
            /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            if (!$input->getOption('table')) {
                $question = new Question('<question>Enter Table Name:</question> ', '');
                $this->addNotEmptyValidator($question);

                $input->setOption(
                    "table",
                    $questionHelper->ask($input, $output, $question)
                );
            }
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
}
