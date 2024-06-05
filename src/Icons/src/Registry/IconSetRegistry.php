<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Registry;

use Symfony\UX\Icons\Icon;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconSetRegistry
{
    /**
     * @param array<string, array{string, array<string, mixed>} $iconSets
     */
    private array $iconSets = [];

    /**
     * @param array<string, array{string, array<string, mixed>} $iconSets
     */
    public function __construct(array $iconSets = [])
    {
        foreach ($iconSets as $name => $data) {
            $path = $data['path'];
            unset($data['path']);
            $this->addIconSet($name, $path, $data);
        }
    }

    public function getAliases(): array
    {
        return array_keys($this->iconSets);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function addIconSet(string $name, string $directory, array $configuration = []): void
    {
        // TODO check name
        // TODO check if directory exists
        // TODO validate configuration

        $this->iconSets[$name] = [
            'directory' => $directory,
            'attributes' => $configuration,
        ];
    }

    public function getIconSetAttributes(string $name): array
    {
        // TODO default attributes
        return $this->iconSets[$name]['attributes'] ?? [];
    }
}
