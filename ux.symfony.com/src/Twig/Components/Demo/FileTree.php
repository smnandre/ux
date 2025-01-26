<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components\Demo;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Demo:FileTree', 'components/Demo/FileTree.html.twig')]
class FileTree
{
    private array $files;
    
    public function __construct(
    ) {
    }
    
    public function getFileList(): array
    {
        return $this->generatePathArray($this->files);
    }
    
    public function getFileTree(): array
    {
        return $this->buildNestedArray($this->files);
    }
    
    public function mount(array $files): void
    {
        $this->files = $files;
    }
    
    public function files(): array
    {
        return $this->generatePathArray($this->files);
    }
    
    private function buildNestedArray(array $paths): array
    {
       $root = [];

    foreach ($paths as $path) {
        $parts = explode('/', $path);
        $currentPath = '';
        $current = &$root;

        foreach ($parts as $index => $part) {
            $currentPath = $currentPath === '' ? $part : $currentPath . '/' . $part;
            $isLastPart = $index === count($parts) - 1;

            if (!isset($current[$part])) {
                $current[$part] = [
                    'filename' => $part,
                    'path' => $currentPath,
                    'isFile' => $isLastPart && strpos($part, '.') !== false,
                    'children' => []
                ];
            }

            $current = &$current[$part]['children'];
        }
    }

    return $root;
    }
    
    private function generatePathArray(array $paths): array
    {
        $result = [];
        $seen = [];
    
        foreach ($paths as $path) {
            $parts = explode('/', $path);
            $currentPath = '';
    
            foreach ($parts as $index => $part) {
                $currentPath = $currentPath === '' ? $part : $currentPath . '/' . $part;
    
                if (!isset($seen[$currentPath])) {
                    $isFile = $index === count($parts) - 1 && str_contains($part, '.');
                    $result[] = ['path' => $currentPath, 'isFile' => $isFile];
                    $seen[$currentPath] = true;
                }
            }
        }
    
        return $result;
    }
}
