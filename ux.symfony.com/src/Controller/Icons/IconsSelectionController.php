<?php

namespace App\Controller\Icons;

use App\Model\Icon\IconSet;
use App\Service\Icon\FavoriteIconSets;
use App\Service\Icon\Iconify;
use App\Service\Icon\IconSetRepository;
use App\Service\Icon\IconSetSampler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconsSelectionController extends AbstractController
{
    #[Route('/icons/selection', name: 'app_icon_selection')]
    public function index(
        IconSetRepository $iconSetRepository,
        FavoriteIconSets $favoriteIconSets,
    ): Response
    {
        $iconSets = array_slice($iconSetRepository->findAll(), 0, 24);

        $favorites = [...iterator_to_array($favoriteIconSets), ...$iconSets];

        $iconSets = array_slice($iconSetRepository->findAll(), 0, 24);
        $iconSets = array_filter($iconSets, fn(IconSet $iconSet) => in_array($iconSet->getIdentifier(), $favorites));

        return $this->render('icons/selection.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }
}
