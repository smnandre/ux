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
    private const CATEGORIES = ['flags', 'emojis', 'brands', 'animated'];

    #[Route('/icons/sets', name: 'app_icon_sets')]
    public function iconSets(IconSetRepository $iconSetRepository): Response
    {
        $iconSets = array_filter($iconSetRepository->findAll(), function (IconSet $iconSet) {
            return !($iconSet->isGeneral() || $iconSet->getPalette());
        });

        usort($iconSets, fn (IconSet $a, IconSet $b) => $a->getName() <=> $b->getName());

        return $this->render('icons/sets/index.html.twig', [
            'iconSets' => $iconSets,
            'categories' => self::CATEGORIES,
        ]);
    }

    #[Route('/icons/sets/{category}', name: 'app_icon_sets_category')]
    public function category(IconSetRepository $iconSetRepository, string $category): Response
    {
        if (!in_array($category, self::CATEGORIES,true)) {
            throw $this->createNotFoundException();
        }

        $iconSets = $iconSetRepository->findAllByCategory($category);

        // TODO specific flags
        $score = fn (IconSet $set) => [str_contains($set->getIdentifier(), 'lag') ? 1 : -1, $set->getTotal()];

        usort($iconSets, fn (IconSet $a, IconSet $b) => $score($b) <=> $score($a));

        return $this->render('icons/sets/category.html.twig', [
            'category' => $category,
            'iconSets' => $iconSets,
            'categories' => self::CATEGORIES,
        ]);
    }
}
