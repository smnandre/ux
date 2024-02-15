<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components\Icon;

use App\Model\Icon\IconSet;
use App\Service\Icon\Iconify;
use App\Service\Icon\IconSetRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('Icon:IconModal')]
class IconModal
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $icon = null;

    private ?string $prefix = null;

    private ?string $name = null;

    private ?IconSet $iconSet = null;

    public function __construct(
        private readonly IconSetRepository $iconSetRepository,
        private readonly Iconify $iconify,
    )
    {
    }

    #[PostHydrate]
    #[PostMount]
    public function postMountHydrate(): void
    {
        if (null === $this->icon) {
            return;
        }

        [$this->prefix, $this->name] = $this->getPrefixName($this->icon);

        $this->iconSet = $this->iconSetRepository->get($this->prefix);
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIconSet(): ?IconSet
    {
        return $this->iconSet;
    }

    public function getSvg(): ?string
    {
        if (null === $this->prefix || null === $this->name) {
            return null;
        }

        return $this->iconify->svg($this->prefix, $this->name);
    }

    private function getPrefixName(string $icon): array
    {
        $parts = explode(':', $icon);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException(sprintf('Invalid icon name "%s".', $icon));
        }

        return $parts;
    }
}
