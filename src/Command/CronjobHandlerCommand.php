<?php

namespace Fatchip\ComputopPayments\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CronjobHandlerCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('computop:start-cronjobs')
            ->setDescription('Starts the cronjobs of the Computop module.')
            ->addArgument(
                'shopId',
                InputArgument::OPTIONAL,
                'ShopID for which die cronjobs will be executed',
                false // Default-Wert, falls nicht angegeben
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

        $iShopId = $input->getArgument('shopId');

        $oScheduler = oxNew(\Fatchip\ComputopPayments\Core\Cronjob\Scheduler::class);
        $oScheduler->start($iShopId);

        return Command::SUCCESS;
    }
}