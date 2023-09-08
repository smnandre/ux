<?php

namespace App\Twig\Tailwind;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

// TODO: I don't want to need to specify name here, but I don't know how to get the class name otherwise
#[AsTwigComponent(name: 'Tailwind:Button')]
class Button
{
    // ref: https://www.radix-ui.com/themes/docs/components/button

    public string $size = 'base';
    // TODO - handle this
    public bool $outline = false; // or variant enum?
    public string $color = 'default'; // primary? Or actual color? Allow null?

    private static array $sizes = [
        'xs' => ['px-3', 'py-2', 'text-xs'],
        'sm' => ['px-3', 'py-2', 'text-sm'],
        'base' => ['px-5', 'py-2.5', 'text-sm'],
        'lg' => ['px-5', 'py-3', 'text-base'],
        'xl' => ['px-6', 'py-3.5', 'text-base'],
    ];

    private static array $colors = [
        'default' => ['text-white', 'bg-blue-700', 'hover:bg-blue-800', 'focus:ring-blue-300', 'dark:bg-blue-600', 'dark:hover:bg-blue-700', 'dark:focus:ring-blue-800'],
        'alternative' => ['text-gray-900', 'bg-white', 'border', 'border-gray-200', 'hover:bg-gray-100', 'hover:text-blue-700', 'focus:ring-gray-200', 'dark:focus:ring-gray-700', 'dark:bg-gray-800', 'dark:text-gray-400', 'dark:border-gray-600', 'dark:hover:text-white', 'dark:hover:bg-gray-700'],
        'green' => ['text-white', 'bg-green-700', 'hover:bg-green-800', 'focus:ring-green-300', 'dark:bg-green-600', 'dark:hover:bg-green-700', 'dark:focus:ring-green-800'],
        'red' => ['text-white', 'bg-red-700', 'hover:bg-red-800', 'focus:ring-red-300', 'dark:bg-red-600', 'dark:hover:bg-red-700', 'dark:focus:ring-red-900'],
        'yellow' => ['text-white', 'bg-yellow-700', 'hover:bg-yellow-800', 'focus:ring-yellow-300', 'dark:bg-yellow-600', 'dark:hover:bg-yellow-700', 'dark:focus:ring-yellow-800'],
        'purple' => ['text-white', 'bg-purple-700', 'hover:bg-purple-800', 'focus:ring-purple-300', 'dark:bg-purple-600', 'dark:hover:bg-purple-700', 'dark:focus:ring-purple-900'],
    ];

    public function calculateClasses(): string
    {
        $classes = ['rounded-lg', 'focus:outline-none', 'font-medium', 'text-center', 'focus:ring-4'];
        $classes = [...$classes, ...self::$sizes[$this->size]];
        $classes = [...$classes, ...self::$colors[$this->color]];

        return implode(' ', $classes);
    }
}
