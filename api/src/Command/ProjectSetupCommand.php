<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'project:setup',
    description: 'Setup project',
)]
class ProjectSetupCommand extends Command
{
    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $resultCount = 0;

        $io->title('Setting up project');

        $resultCount += $this->getApplication()
            ->find('project:generate-keys')
            ->run(new ArrayInput([]), $output);

        $resultCount += $this->getApplication()
            ->find('project:seed-database')
            ->run(new ArrayInput([]), $output);

        $io->newLine(2);
        $io->info('Initial user credentials');
        $io->text([
            'Email: user@example.com',
            'Password: secret',
            'Bearer token:'
        ]);

        $resultCount += $this->getApplication()
            ->find('lexik:jwt:generate-token')
            ->run(new ArrayInput(['username' => 'user@example.com']), $output);

        if ($resultCount == 0) {
            $io->success('Project configured successfully. Check credentials above.');

            return Command::SUCCESS;
        }

        $io->error('Error occurred while trying to configure project. Check output above.');

        return Command::FAILURE;
    }
}
