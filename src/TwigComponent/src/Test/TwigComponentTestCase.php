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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 *
 * @template T of object
 *
 * @author Simon André <smn.andre@gmail.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class TwigComponentTestCase extends KernelTestCase
{
    // ex: anonymous component
    // --> name ->  include template
    // --> name + blocks -> embed template
    // --> define context
    // --> 

    // --> mock
    
    
    protected function setUp(): void
    {
        self::bootKernel();
        
        // Create Factory
        
        // Create ComponentRenderer
        
        // Create Loader
        
        // Create Environment
        
        // Create ComponentFactory
        
        // Create ComponentRenderer
        
    }
    

    protected function foo(): void
    {
        $twig = self::getContainer()->get('twig');

        $loader = new \Twig\Loader\ArrayLoader([
            'index' => 'Hello {{ name }}',
        ]);
        $twig = new Environment($loader);
        $twig->addExtension($twig->getExtensions());
    }

    // register component template and/or class

    // render (name, data, content, blocks)

    // render(name, data)

    // render(name, data, blocks)

    // array loader

    // callback loader
    // 
    
    
    private function createLoader(): LoaderInterface
    {
        return new class() implements LoaderInterface {
            public function getSourceContext($name): \Twig\Source
            {
                return new \Twig\Source('Hello {{ name }}', 'index');
            }

            public function getCacheKey($name): string
            {
                return 'index';
            }

            public function isFresh($name, $time): bool
            {
                return true;
            }

            public function exists($name): true
            {
                return true;
            }
        };
    }
    
    
}

class ComponentLoader implements LoaderInterface
{
    public function getSourceContext($name): \Twig\Source
    {
        return new \Twig\Source('Hello {{ name }}', 'index');
    }

    public function getCacheKey($name): string
    {
        return 'index';
    }

    public function isFresh($name, $time): bool
    {
        return true;
    }

    public function exists($name): true
    {
        return true;
    }
}
