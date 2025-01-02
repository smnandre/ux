<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Test;

use Symfony\UX\TwigComponent\ComponentRendererInterface;

final class MockComponentRenderer implements ComponentRendererInterface
{
    /**
     * @param array<string, array<string|callable>> $components
     */
    private function __construct(
        private array $components = []
    ) {
    }
    
    public function createAndRender(string $name, array $props = []): string
    {
        if (isset($this->components[$name])) {
            $component = $this->components[$name];
            
            if (is_callable($component)) {
                return $component($props);
            }
            
            return $component;
        }
        
        
        // If the component is not found in the components array, return an empty string
        return '';
    }
    
    public static function create(array $components = []): self
    {
        return new self();
    }
}
