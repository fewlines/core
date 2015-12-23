<?php

namespace Fewlines\Core\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
	protected function configure() {

	}

	/**
	 * @param InputInterface $input
	 * @param OutputInteface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

	}
}