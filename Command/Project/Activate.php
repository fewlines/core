<?php

namespace Fewlines\Core\Command\Project;

use Fewlines\Core\Command\Command;
use Fewlines\Core\Application\ProjectManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Activate extends Command
{
	protected function configure() {
		$this->setName('project:activate');
        $this->setDescription('Activates a project');

        $this->addArgument(
        	'id', InputArgument::REQUIRED, 'A unique id of your project'
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$id = $input->getArgument('id');
		$result = ProjectManager::activateProject($id);

		if ($result) {
        	$output->writeln(sprintf('<info>Activated project "%s"</info>', $id));
        }
        else {
        	$output->writeln('<error>Something went wrong</error>');
        }
	}
}