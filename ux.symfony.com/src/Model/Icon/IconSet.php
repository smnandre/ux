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

class IconSet
{
    // https://api.github.com/repos/tandpfun/skill-icons

    // https://iconify.design/docs/types/iconify-info.html#structure
    public function __construct(
        private string $identifier,
        private string $name,
        private array $author,
        private array $license,
        private ?int $total = null,
        private ?string $version = null,
        private ?array $samples = [],
        private array|int|null $height = [],
        private ?int $displayHeight = null,
        private ?string $category = null,
        private ?array $tags = [],
        private ?bool $palette = null,
        private ?array $suffixes = [],
        private ?array $categories = [],
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuthor(): array
    {
        return $this->author;
    }

    public function getLicense(): array
    {
        return $this->license;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getSamples(): array
    {
        return $this->samples;
    }

    public function getHeight(): array|int|null
    {
        return $this->height;
    }

    public function getDisplayHeight(): ?int
    {
        return $this->displayHeight;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function getPalette(): ?bool
    {
        return $this->palette;
    }

    public function getSuffixes(): ?array
    {
        return $this->suffixes;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function getIndex(): int
    {
        return abs(crc32($this->identifier)) % 100;
    }

}
