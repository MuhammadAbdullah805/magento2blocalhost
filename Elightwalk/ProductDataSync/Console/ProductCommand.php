<?php
declare(strict_types=1);


namespace Elightwalk\ProductDataSync\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductCommand extends Command
{
    const PATH_ARGUMENT = 'path';

    const INVENTORY = 'inventory';

    protected $_syncProducts;

    public function __construct(
        \Elightwalk\ProductDataSync\Model\Handler\SyncProducts $syncProducts,
        ?string $name = null
    )
    {
        $this->_syncProducts = $syncProducts;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('sync:products')
            ->setDescription('Import Product from CSV')
            ->setDefinition([
                new InputArgument(
                    self::INVENTORY,
                    InputArgument::OPTIONAL,
                    'Inventory'
                ),
            ]);
            //->setDefinition([
            //    new InputArgument(
           //         self::PATH_ARGUMENT,
           //         InputArgument::REQUIRED,
            //        'path'
           //    )
            //]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            $inventory = $input->getArgument(self::INVENTORY);

            $argument=[
                'inventory' => $inventory
            ];

            //$path = $input->getArgument(self::PATH_ARGUMENT);
            $this->_syncProducts->initialization($argument);

        } catch (\Exception $ex) {
            $output->writeln('Something went wrong in the code:');
            $output->writeln('Exception: ' . $ex->getMessage());
        }
    }
}
