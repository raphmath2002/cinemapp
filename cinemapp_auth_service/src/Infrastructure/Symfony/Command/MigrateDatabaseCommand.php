<?php

namespace Infrastructure\Symfony\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:migrate')]
class MigrateDatabaseCommand extends Command
{

    public function __construct(
        private KernelInterface $appKernel,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->appKernel->getProjectDir();

        $searchString = "$projectDir\\migrations\\*.sql";

        try {
            $migrationFiles = glob($searchString);
            $connection = $this->em->getConnection();

            foreach ($migrationFiles as $migrationFile) {
                $migrationFileContent = file_get_contents($migrationFile);
                
                print_r("[INFO]: Executing $migrationFile\n");
                $connection->executeQuery($migrationFileContent);
            }

            print_r("Done\n");

        } catch (\Exception $e) {
            throw $e;
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}