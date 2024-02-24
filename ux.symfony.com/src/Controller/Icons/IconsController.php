<?php

namespace App\Controller\Icons;

use App\Service\Icon\FavoriteIconSets;
use App\Service\Icon\Iconify;
use App\Service\Icon\IconSetRepository;
use App\Service\Icon\IconSetSampler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IconsController extends AbstractController
{
    #[Route('/icons', name: 'app_icons')]
    public function index(FavoriteIconSets $favoriteIconSets): Response
    {
        return $this->render('icons/index.html.twig', [
            'iconSets' => [...$favoriteIconSets],
        ]);
    }

    #[Route('/icons/{prefix}', name: 'app_icon_set')]
    public function iconSet(string $prefix, IconSetRepository $iconSetRepository, Iconify $iconify, IconSetSampler $iconSetSampler): Response
    {
        // TODO use SetSampler in Repo/Factory
        $iconSet = $iconSetRepository->find($prefix);
        if (null === $iconSet) {
            throw $this->createNotFoundException(sprintf('IconSet not found for prefix "%s".', $prefix));
        }

        return $this->render('icons/icon_set.html.twig', [
            'prefix' => $prefix,
            'iconSet' => $iconSet,
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'categories' => $iconify->collectionCategories($prefix),
            'icons' => $iconify->collectionIcons($prefix),
            'samples' => $iconSetSampler->getSampleIcons($iconSet),
        ]);
    }
}
