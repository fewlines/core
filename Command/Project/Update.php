<?php

namespace Fewlines\Core\Command\Project;

use Fewlines\Core\Command\Command;
use Fewlines\Core\Application\ProjectManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command
{
	protected function configure() {
		$this->setName('project:update');
		$this->setDescription('Updates a project');

		$this->addArgument(
        	'id', InputArgument::REQUIRED, 'The id of the project to change'
        );

        $this->addArgument(
        	'property', InputArgument::REQUIRED, 'The property of the project to change'
        );

        $this->addArgument(
        	'value', InputArgument::REQUIRED, 'The value of the given property'
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$id = $input->getArgument('id');

		$result = ProjectManager::updateProject(
			$id,
			$input->getArgument('property'),
			$input->getArgument('value')
		);

		if ($result) {
			$output->writeln(
				sprintf('<info>Project "%s" updated successfully</info>', $id)
			);
		}
		else {
			$output->writeln('<error>Something went wrong</error>');
		}
	}
}