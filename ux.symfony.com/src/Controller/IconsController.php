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
        return $this->render('icons/collection.html.twig', [
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'prefix' => $prefix,
        ]);
    }
}
