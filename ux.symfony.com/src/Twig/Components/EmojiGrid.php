<?php

namespace App\Twig\Components;

use App\Service\EmojiRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('EmojiGrid')]
final class EmojiGrid
{
    use DefaultActionTrait;

    private const PER_PAGE = 12;

    #[LiveProp]
    public string $category = 'animal';

    #[LiveProp]
    public int $page = 1;

    #[LiveProp]
    public int $perPage = self::PER_PAGE;

    public function __construct(
        private readonly EmojiRepository $emojiRepository)
    {
    }

    public function getEmojis(): array
    {
        $emojis = match ($this->category) {
            'animal' => $this->emojiRepository->getAnimalEmojis(),
            'nature' => $this->emojiRepository->getNatureEmojis(),
            'food' => $this->emojiRepository->getFoodEmojis(),
            default => throw new \LogicException(sprintf('Unknown category: "%s"', $this->category)),
        };

        if ($this->page > 1) {
            sleep(20);
        }

        $offset = ($this->page - 1) * $this->perPage;
        if ($offset >= \count($emojis)) {
            throw new \RuntimeException(sprintf('Invalid page: "%d"', $this->page));
        }

        return array_slice($emojis, $offset, $this->perPage);
    }

    #[LiveAction]
    public function prev(): void
    {
        $this->page--;
    }

    #[LiveAction]
    public function next(): void
    {
        $this->page++;
    }
}
