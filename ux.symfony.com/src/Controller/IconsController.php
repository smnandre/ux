<?php

namespace App\Controller;

use App\Iconify;
use App\Model\Icon\IconSet;
use App\Service\Icon\IconSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IconsController extends AbstractController
{
    #[Route('/icons', name: 'app_icons')]
    public function index(IconSetRepository $iconSetRepository): Response
    {

        $iconSets = array_slice($iconSetRepository->findAll(), 0, 12);

        return $this->render('icons/index.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }

    #[Route('/icons/sets', name: 'app_icon_sets')]
    public function collections(IconSetRepository $iconSetRepository): Response
    {
        $iconSets = $iconSetRepository->findAll();
        $iconSets = array_filter($iconSets, function(IconSet $iconSet) {
            if (str_contains($iconSet->getName(), 'crypto')) {
                return false;
            }
            if (str_contains($iconSet->getName(), 'moji')) {
                return false;
            }
            if ($iconSet->getPalette()=== true) {
                return false;
            }
            return true;
        });

        return $this->render('icons/sets.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }

    #[Route('/icons/{prefix}', name: 'app_icon_collection')]
    public function collection(string $prefix, IconSetRepository $iconSetRepository, Iconify $iconify): Response
    {
        if (2 === count($parts = explode(':', $prefix))) {
            return $this->redirectToRoute('app_icon', ['prefix' => $parts[0], 'name' => $parts[1]]);
        }

        $iconSet = $iconSetRepository->find($prefix);
        if (null === $iconSet) {
            throw $this->createNotFoundException(sprintf('IconSet not found for prefix "%s".', $prefix));
        }

        return $this->render('icons/collection.html.twig', [
            'prefix' => $prefix,
            'iconSet' => $iconSet,
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'categories' => $iconify->collectionCategories($prefix),
            'icons' => $iconify->collectionIcons($prefix),
        ]);
    }

    #[Route('/icons/{prefix}/{name}', name: 'app_icon')]
    public function icon(string $prefix, string $name, IconSetRepository $iconSetRepository, Iconify $iconify): Response
    {
        $iconSet = $iconSetRepository->find($prefix);
        if (null === $iconSet) {
            throw $this->createNotFoundException(sprintf('IconSet not found for prefix "%s".', $prefix));
        }


        return $this->render('icons/icon.html.twig', [
            'prefix' => $prefix,
            'name' => $name,
            'iconSet' => $iconSet,
            'collection' => $iconify->collection($prefix) ?? throw $this->createNotFoundException(),
            'svg' => $iconify->svg($prefix, $name) ?? throw $this->createNotFoundException(),
            'fullName' => "{$prefix}:{$name}",
        ]);
    }
}
