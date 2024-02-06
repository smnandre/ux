<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Icon;

class Metadata
{
    // https://iconify.design/docs/types/iconify-info.html#structure
    public function __construct(
        private string $name,
        private array $author,
        private array $licence,
        private ?int $total = null,
        private ?string $version = null,
        private ?array $samples = [],
        private ?int $height = null,
        private ?int $displayHeight = null,
        private ?string $category = null,
        private ?array $tags = [],
        private ?string $palette = null,
    ) {
    }

      // $iconifyInfo['height'] ?? null,
      //       $iconifyInfo['displayHeight'] ?? null,
      //       $iconifyInfo['category'] ?? null,
      //       $iconifyInfo['tags'] ?? [],
      //       $iconifyInfo['palette'] ?? null,

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIconCount(): int
    {
        return $this->iconCount;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getAuthor(): array
    {
        return $this->author;
    }

    public function getLicence(): array
    {
        return $this->licence;
    }

    public function getSamples(): array
    {
        return $this->samples;
    }
}
