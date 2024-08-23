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

use App\Service\CookbookFactory;
use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    #[Route('/documentation', name: 'app_documentation')]
    public function __invoke(UxPackageRepository $packageRepository): Response
    {
        $packages = $packageRepository->findAll();
        usort($packages, fn ($a, $b) => $a->getHumanName() <=> $b->getHumanName());

        return $this->render('documentation/index.html.twig', [
            'packages' => $packages,
        ]);
    }

    #[Route('/documentation/{slug}', name: 'app_documentation_package')]
    public function package(string $slug, UxPackageRepository $packageRepository): Response
    {
        if (null === $package = $packageRepository->find($slug)) {
            throw $this->createNotFoundException();
        }

        if (!file_exists($file = __DIR__.'/../../docs/'.$slug.'.md')) {
            throw $this->createNotFoundException();
        }

        $content = file_get_contents($file);
        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        // Remove first line (title)
        $content = explode("\n\n", $content, 2)[1];

        return $this->render('documentation/package.html.twig', [
            'package' => $package,
            'content' => $content,
        ]);
    }

}
