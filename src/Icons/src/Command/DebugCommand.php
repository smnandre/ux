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
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Iconify;
use Symfony\UX\Icons\IconRegistryInterface;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * A console command to debug UX Icons configuration and icons.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
#[AsCommand(
    name: 'ux:icons:debug',
    description: 'Display icon and icon sets information',
)]
final class DebugCommand extends Command
{
    public function __construct(
        private IconRegistryInterface $registry,
        private array $defaultIconAttributes = [],
        private array $renderAttributes = [],
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('icon', InputArgument::OPTIONAL, 'The icon name'),
            ])
            ->setHelp(<<<'EOF'
  <info>php %command.full_name%</info>

The command lists all available icon sets.

  <info>php %command.full_name% bi:check</info>

The command display information and rendering attributes for the bi:check icon.

EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $icon = $input->getArgument('icon');
        [$prefix, $name] = explode(':', $icon, 2);

        $svgIcon = $this->registry->get($icon);

        $io->definitionList(
            new TableSeparator(),
            $svgIcon->getAttributes(),
            new TableSeparator(),
            'Inner SVG',
            new TableSeparator(),
            ['nar' => $this->formatIcon($io, 'addd:bar')],
            ['foo' => '<href=https://symfony.com>Symfony Homepage</>'],
            ['svg' => substr($svgIcon->getInnerSvg(),  0, 40).'...'],
        );
        $io->createTable()
            ->setHeaders(['Icon', 'Prefix', 'Name', 'Attributes'])
            ->setRows([
                [$icon, $prefix, $name, implode("\n", $svgIcon->getAttributes())],
            ])
            ->setHeaderTitle('sdfsdfsf')
            ->setFooterTitle('dd')
            ->render();

        $io->table(['foo', 'bar'], [
            ['nar' => 'foo', 'bar' => 'bar'],
            ['nar' => 'foo', 'bar' => 'bar'],
        ]);

        return Command::SUCCESS;
    }

    private function formatIcon(OutputInterface $output, string $icon): string
    {
        if (!$output->getFormatter()->hasStyle('icon-prefix')) {
            $output->getFormatter()->setStyle('icon-prefix', new OutputFormatterStyle('bright-white', 'black'));
        }
        if (!$output->getFormatter()->hasStyle('icon-name')) {
            $output->getFormatter()->setStyle('icon-name', new OutputFormatterStyle('bright-magenta', 'black'));
        }

        [$prefix, $name] = explode(':', $icon.':');

        return sprintf('<icon-prefix> %s:</><icon-name>%s </>', $prefix, $name);
    }
}
