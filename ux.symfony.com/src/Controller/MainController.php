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

use App\Model\RecipeFileTree;
use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(UxPackageRepository $packageRepository): Response
    {
        $packages = $packageRepository->findAll();

        return $this->render('main/homepage.html.twig', [
            'packages' => $packages,
            'recipeFileTree' => new RecipeFileTree(),
        ]);
    }
    
    #[Route('/test/{method}/{nb}', name: 'app_test_component')]
    public function testComponent(string $method = 'twig', int $nb = 100): Response
    {
        $methods = ['twig', 'twig_embed', 'anonymous', 'anonymous_embed', 'php', 'php_embed'];
        if (!\in_array($method, $methods, true)) {
            throw $this->createNotFoundException();
        }
        
        $iterations = [10, 50, 100, 500, 1000, 5000];
        if (!\in_array($nb, $iterations, true)) {
            throw $this->createNotFoundException();
        }
        
        return $this->render('test_components.html.twig', [
            'iterations' => $iterations,
            'nb' => $nb,
            'methods' => $methods,
            'method' => $method,
        ]);
    }

    #[Route(path: '/robots.txt', name: 'app_robots')]
    public function __invoke(Request $request): Response
    {
        $response = $this->render('robots.txt.twig');
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
