<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'project:generate-keys',
    description: 'Generate JWT keys',
)]
class ProjectGenerateKeysCommand extends Command
{
    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Generating JWT keys');

        $output = null;

        exec(
            "docker-compose exec php sh -c '
                set -e
                apk add openssl
                php bin/console lexik:jwt:generate-keypair --skip-if-exists
                setfacl -R -m u:www-data:rX -m u:`$(whoami)`:rwX config/jwt
                setfacl -dR -m u:www-data:rX -m u:`$(whoami)`:rwX config/jwt
            '",
            $output
        );

        $io->text($output);

        return Command::SUCCESS;
    }
}
