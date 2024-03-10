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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Iconify;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * A console command to search icons and icon sets from iconify.design
 *
 * @author Simon André <smn.andre@gmail.com>
 */
#[AsCommand(
    name: 'ux:icons:search',
    description: 'Search icons and sets from iconify.design',
)]
final class SearchIconCommand extends Command
{
    public function __construct(private Iconify $iconify)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('prefix', InputArgument::REQUIRED, 'Prefix or name of the icon set')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the icon (leave empty to search for sets)')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getArgument('prefix')) {
            $io = new SymfonyStyle($input, $output);
            $io->title('Search icons and sets from iconify.design');
            $io->ask('Prefix or name of the icon set', null, function ($prefix) use ($input) {
                $input->setArgument('prefix', $prefix);
            });
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $prefix = $input->getArgument('prefix');

        if (null === $name = $input->getArgument('name')) {
            $this->searchIconSets($io, $prefix);

            return Command::SUCCESS;
        }

        $iconSet = $this->iconify->getIconSets()[$prefix] ?? null;
        if (!$iconSet) {
            $iconSets = [];
            foreach ($this->iconify->getIconSets() as $key => $iconSet) {
                if (str_contains(strtolower($iconSet['name']), $prefix)) {
                    $iconSets[$key] = $iconSet;
                }
            }
            if (count($iconSets) > 1) {
                $io->error(sprintf('Multiple icon sets found for prefix "%s". Please be more specific.', $prefix));
                $io->listing(array_keys($iconSets));

                return Command::INVALID;
            }
            reset($iconSets);
            $prefix = key($iconSets);
        }

        // loader!!
        $io->writeln(sprintf('Searching <comment>%s</comment> in <comment>%s</comment> set' , $name, $prefix));
        $results = $this->iconify->searchIcons($prefix, $name);

        // total / limit / start
        $icons = array_map(fn($icon) => str_replace($name, '<fg=bright-blue>'.$name.'</>', $icon), $results['icons'] ?? []);

        $io->listing($icons);

        // $io->table([
        //     sprintf('Search: %s', $results['total'] ?? 0),
        // ],
        //     array_chunk($icons, 3)
        // );

        return Command::SUCCESS;
    }

    private function searchIconSets(SymfonyStyle $io, string $query): void
    {
        $results = [];
        //$io->title('Results for "'.$query.'"');
        $query = mb_strtolower($query);
        foreach ($this->iconify->getIconSets() as $prefix => $metadata) {
            if (!str_contains($prefix, $query) && !str_contains(mb_strtolower($metadata['name']), $query)) {
                continue;
            }
            $results[] = [
                $prefix,
                $metadata['name'],
                $metadata['total'],
                $metadata['license']['title'] ?? '',
                $this->formatIcon($io, $prefix.':'.$metadata['samples'][0]),
            ];
        }
        $io->table(['Prefix', 'Name', 'Icons', 'Licence', 'Example'], $results);
    }

    private function searchIcons(string $query): void
    {
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if (!$input->mustSuggestArgumentValuesFor('prefix')) {
            return;
        }

        $prefixes = array_keys($this->iconify->getIconSets());
        if ($input->getArgument('prefix')) {
            $prefixes = array_filter($prefixes, fn($prefix) => str_contains($prefix, $input->getArgument('prefix')));
        }

        $suggestions->suggestValues($prefixes);
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
