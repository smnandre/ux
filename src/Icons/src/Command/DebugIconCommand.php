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
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Registry\IconSetRegistry;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:debug',
    description: 'Debug icon(s) configuration.'
)]
final class DebugIconCommand extends Command
{
    public function __construct(
        private IconSetRegistry $iconSets,
        private LocalSvgIconRegistry $localSvgIcons
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Icon sets');

        foreach ($aliases = $this->iconSets->getAliases() as $alias) {
            $io->section($alias);
            $io->table(['Name', 'Path'], [
                [$alias, '??']
            ]);
            $io->definitionList('Attributes', $this->iconSets->getIconSetAttributes($alias));
            $this->iconSets->getIconSetAttributes($alias);
        }

        return Command::SUCCESS;
    }
}
