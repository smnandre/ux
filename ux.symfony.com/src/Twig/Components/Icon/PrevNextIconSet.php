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
use App\Service\Icon\IconSetRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Icon:PrevNextIconSet')]
class PrevNextIconSet
{
    public ?IconSet $iconSet = null;

    public function __construct(
        private readonly IconSetRepository $iconSetRepository,
    ) {
    }

    public function getPrevious(bool $loop = false): ?IconSet
    {
        if ($this->iconSet === null) {
            return $loop ? $this->iconSetRepository->getLast() : null;
        }

        return $this->iconSetRepository->getPrevious($this->iconSet->getIdentifier(), $loop);
    }

    public function getNext(bool $loop = false): ?IconSet
    {
        if (null === $this->iconSet) {
            return $loop ? $this->iconSetRepository->getFirst() : null;
        }

        return $this->iconSetRepository->getNext($this->iconSet->getIdentifier(), $loop);
    }
}
