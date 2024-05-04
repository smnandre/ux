<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function __invoke(): Response
    {
        $twig = $this->container->get('twig');
        $twig->getLoader()->addPath(__DIR__.'/../../demo', 'Demo');

        return $this->render('@Demo/Live/Basic/templates/foo.html.twig', ['foo' => 'bar']);
    }
}
