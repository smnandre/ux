<?php

namespace App\Service;

final class EmojiRepository
{
    /**
     * @return array<string>
     */
    public function getAllEmojis(): array
    {
        return [
            ...$this->getAnimalEmojis(),
            ...$this->getNatureEmojis(),
            ...$this->getFoodEmojis(),
        ];
    }

    /**
     * @return array<string>
     */
    public function getNatureEmojis(): array
    {
        return [
            '🌸', '💮', '🏵️', '🌹', '🥀', '🌺', '🌻', '🌼', '🌷', '🌱',
            '🌲', '🌳', '🌴', '🌵', '🌾', '🌿', '☘️', '🍀', '🍁', '🍂',
            '🍃', '🍄',
        ];
    }

    /**
     * @return array<string>
     */
    public function getAnimalEmojis(): array
    {
        return [
            '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '🙈',
            '🙉', '🦝', '🐵', '🐒', '🦍', '🐶', '🐕', '🐩', '🐺', '🦊',
            '🐱', '🐈', '🦁', '🐯', '🐅', '🐆', '🐴', '🐎', '🦄', '🦓',
            '🦌', '🐮', '🦙', '🐂', '🐃', '🐄', '🐷', '🦛', '🐖', '🐗',
            '🐽', '🐏', '🐑', '🐐', '🐪', '🐫', '🦒', '🐘', '🦏', '🐭',
            '🐁', '🐀', '🦘', '🐹', '🦡', '🐰', '🐇', '🐿️', '🦔', '🦇',
            '🐻', '🐨', '🐼', '🐾', '🦃', '🐔', '🦢', '🐓', '🐣', '🐤',
            '🦚', '🐥', '🐦', '🦜', '🐧', '🕊️', '🦅', '🦆', '🦉', '🐸',
            '🐊', '🐢', '🦎', '🐍', '🐲', '🐉', '🦕', '🦖', '🐳', '🐋',
            '🐬', '🐟', '🐠', '🐡', '🦈', '🐙', '🐚', '🦀', '🦟', '🦐',
            '🦑', '🦠', '🐌', '🦋', '🐛', '🐜', '🐝', '🐞', '🦗', '🕷️',
            '🕸️', '🦂', '🦞',
        ];
    }

    /**
     * @return array<string>
     */
    public function getFoodEmojis(): array
    {
        return [
            '🥭', '🍇', '🍈', '🍉', '🍊', '🍋', '🍌', '🍍', '🍎', '🍏',
            '🍐', '🍑', '🍒', '🥬', '🍓', '🥝', '🍅', '🥥', '🥑', '🍆',
            '🥔', '🥕', '🌽', '🌶', '🥯', '🥒', '🥦', '🥜', '🌰', '🍞',
            '🥐', '🥖', '🥨', '🥞', '🧀', '🍖', '🍗', '🥩', '🥓', '🍔',
            '🍟', '🍕', '🌭', '🥪', '🌮', '🌯', '🥙', '🥚', '🧂', '🍳',
            '🥘', '🍲', '🥣', '🥗', '🍿', '🥫', '🍱', '🍘', '🍙', '🍚',
            '🍛', '🍜', '🥮', '🍝', '🍠', '🍢', '🍣', '🍤', '🍥', '🍡',
            '🥟', '🥠', '🥡', '🍦', '🍧', '🍨', '🍩', '🍪', '🧁', '🎂',
            '🍰', '🥧', '🍫', '🍬', '🍭', '🍮', '🍯', '🍼', '🥛', '☕',
            '🍵', '🍶', '🍾', '🍷', '🍸', '🍹', '🍺', '🍻', '🥂', '🥃',
            '🥤', '🥢', '🍽️', '🍴', '🥄', '🏺',
        ];
    }
}
