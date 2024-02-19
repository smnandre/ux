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
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Registry\CacheIconRegistry;
use Symfony\UX\Icons\Twig\IconFinder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:warm-cache',
    description: 'Warm the icon cache',
)]
final class WarmCacheCommand extends Command
{
    public function __construct(private CacheIconRegistry $registry, private IconFinder $icons)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment('Warming the icon cache...');

        foreach ($this->icons->icons() as $icon) {
            try {
                $this->registry->get($icon, refresh: true);

                if ($output->isVerbose()) {
                    $io->writeln(sprintf(' Warmed icon <comment>%s</comment>.', $icon));
                }
            } catch (IconNotFoundException) {
            }
        }

        $io->success('Icon cache warmed.');

        return Command::SUCCESS;
    }
}
