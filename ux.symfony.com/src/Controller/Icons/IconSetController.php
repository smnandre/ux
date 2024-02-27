<?php

namespace App\Controller\Icons;

use App\Model\Icon\IconSet;
use App\Service\Icon\IconSetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * @author Simon André <smn.andre@gmail.com>
 */
final class IconSetController extends AbstractController
{
    private const PER_PAGE = 24;

    #[Route('/icons/sets', name: 'app_icon_sets')]
    public function collections(IconSetRepository $iconSetRepository): Response
    {
        $iconSets = array_filter($iconSetRepository->findAll(), function (IconSet $iconSet) {
            return !($iconSet->isGeneral() || $iconSet->getPalette());
        });

        usort($iconSets, fn (IconSet $a, IconSet $b) => $b->getTotal() <=> $a->getTotal());

        return $this->render('icons/sets/index.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }

    #[Route('/icons/sets/search', name: 'app_icon_sets_search')]
    public function all(IconSetRepository $iconSetRepository): Response
    {
        $nbPerPage = self::PER_PAGE;
        $page = 1;

        return $this->render('icons/sets/search.html.twig', [
            'iconSets' => array_slice($iconSetRepository->findAll(), $page * $nbPerPage, $nbPerPage),
        ]);
    }

    #[Route('/icons/sets/brands', name: 'app_icon_sets_brands')]
    public function brands(IconSetRepository $iconSetRepository): Response
    {
        $allIconSets = $iconSetRepository->findAll();
        $iconSets = array_filter($allIconSets, fn (IconSet $iconSet) => $iconSet->isBrandsSocial());
        usort($iconSets, fn (IconSet $a, IconSet $b) => $b->getTotal() <=> $a->getTotal());

        return $this->render('icons/sets/brands.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }

    #[Route('/icons/sets/emojis', name: 'app_icon_sets_emojis')]
    public function emojis(IconSetRepository $iconSetRepository): Response
    {
        $allIconSets = $iconSetRepository->findAll();
        $iconSets = array_filter($allIconSets, fn (IconSet $iconSet) => $iconSet->isEmoji());
        usort($iconSets, fn (IconSet $a, IconSet $b) => $b->getTotal() <=> $a->getTotal());

        return $this->render('icons/sets/emojis.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }

    #[Route('/icons/sets/flags', name: 'app_icon_sets_flags')]
    public function flags(IconSetRepository $iconSetRepository): Response
    {
        $allIconSets = $iconSetRepository->findAll();
        $iconSets = array_filter($allIconSets, fn (IconSet $iconSet) => $iconSet->isMapsFlags());
        usort($iconSets, fn (IconSet $a, IconSet $b) => $b->getTotal() <=> $a->getTotal());

        return $this->render('icons/sets/flags.html.twig', [
            'iconSets' => $iconSets,
        ]);
    }
}
