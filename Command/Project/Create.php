<?php

namespace Fewlines\Core\Command\Project;

use Fewlines\Core\Command\Command;
use Fewlines\Core\Application\ProjectManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends Command
{
	protected function configure() {
		$this->setName('project:create');
        $this->setDescription('Creates a new project');

        $this->addArgument(
        	'id', InputArgument::REQUIRED, 'A unique id of your project'
        );

        // Namespace
		$this->addArgument(
			'namespace', InputArgument::REQUIRED, 'The namespace of your classes'
		);

        // Name
		$this->addArgument(
			'name', InputArgument::OPTIONAL, 'The name of your project'
		);

		// Description
		$this->addArgument(
			'description', InputArgument::OPTIONAL, 'The description of your project'
		);

		// Auto activate
        $this->addOption(
			'activate', null, InputOption::VALUE_NONE, 'If set, the project will be activated automatically'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$id = $input->getArgument('id');
		$name = $input->getArgument('name');
		$namespace = $input->getArgument('namespace');
		$description = $input->getArgument('description');
		$active = $input->getOption('activate');

		if (empty($name)) {
			$name = $id . ' name';
		}

		if (empty($description)) {
			$description = $id . ' description';
		}

		ProjectManager::createProject($id, $name, $description, $namespace, $active, $output);

        $output->writeln(sprintf('<info>Created project "%s"</info>', $id));
	}
}