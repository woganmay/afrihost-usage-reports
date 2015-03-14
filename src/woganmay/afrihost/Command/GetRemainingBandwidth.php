<?php

namespace WoganMay\Afrihost\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetRemainingBandwidthCommand extends Command
{
  protected function configure()
  {
      $this
          ->setName('remaining')
          ->setDescription('Show the remaining bandwidth on all ADSL packages')
          ->addArgument(
              'username',
              InputArgument::REQUIRED,
              'Afrihost ClientZone Username (usually an email address)'
          )
          ->addArgument(
              'password',
              InputArgument::REQUIRED,
              'Afrihost ClientZone Password'
          );
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
      $username = $input->getArgument('username');
      $password = $input->getArgument('password');

      // Authenticate

      // Scrape

      $output->writeln($text);
  }

}
