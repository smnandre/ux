<?php

namespace App\Controller;

use App\Iconify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IconsController extends AbstractController
{
    #[Route('/icons', name: 'app_icons')]
    public function index(): Response
    {
        return $this->render('icons/index.html.twig');
    }

    #[Route('/icons/{prefix}', name: 'app_icon_collection')]
    public function collection(string $prefix, Iconify $iconify): Response
    {
        if (2 === count($parts = explode(':', $prefix))) {
            return $this->redirectToRoute('app_icon', ['prefix' => $parts[0], 'name' => $parts[1]]);
        }

        return $this->render('icons/collection.html.twig', [
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'prefix' => $prefix,
        ]);
    }

    #[Route('/icons/{prefix}/{name}', name: 'app_icon')]
    public function icon(string $prefix, string $name, Iconify $iconify): Response
    {
        return $this->render('icons/icon.html.twig', [
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'svg' => $iconify->svg($prefix, $name) ?? throw $this->createNotFoundException(),
            'prefix' => $prefix,
            'name' => $name,
            'fullName' => "{$prefix}:{$name}",
        ]);
    }
}
