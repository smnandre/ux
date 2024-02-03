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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:lint',
    description: 'Lint SVG icons and outputs encountered errors',
)]
final class LintIconCommand extends Command
{
    public function __construct(
        private readonly string $iconDir,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'A directory')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command lints a svg icon and outputs to STDOUT
the first encountered error.

You can validate all the icons

  <info>php %command.full_name% </info>

Or a specific directory:

  <info>php %command.full_name% dirname</info>

EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $directory = $this->iconDir;
        if ($input->hasArgument('path')) {
            $directory = $input->getArgument('path') ?: $directory;
        }

        $registry = new LocalSvgIconRegistry($directory);

        $iconCount = 0;
        $errorCount = 0;

        foreach ($io->progressIterate($registry) as $name) {
            $iconCount++;
            try {
                $registry->get($name);
            } catch (\Throwable $e) {
                $errorCount++;
                $io->error('Invalid icon: '.$name ."\n".$e->getMessage());
            }
        }

        if (0 !== $errorCount) {
            $io->error(sprintf('Some icons contain errors (%d / %d).', $errorCount, $iconCount));

            return Command::FAILURE;
        }

        $io->success(sprintf('All icons are valid (%d).', $iconCount));

        return Command::SUCCESS;
    }
}
