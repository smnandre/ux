<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:list',
    description: 'List available icons',
)]
final class ListIconsCommand extends Command
{
    public function __construct(private IconRegistryInterface $localRegistry)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $localIcons = iterator_to_array($this->localRegistry);

        if ($localIcons) {
            $io->title('Local SVG Icons');

            $io->table(
                ['Name'],
                array_map(static fn (string $icon) => [$icon], $localIcons),
            );
        } else {
            $io->warning('No local SVG icons found.');
        }

        return Command::SUCCESS;
    }
}
