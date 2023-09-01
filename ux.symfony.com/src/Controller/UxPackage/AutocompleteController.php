<?php

namespace App\Controller\UxPackage;

use App\Entity\Food;
use App\Form\TimeForAMealForm;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AutocompleteController extends AbstractController
{
    #[Route('/autocomplete', name: 'app_autocomplete')]
    public function autocomplete(Request $request): Response
    {
        $form = $this->createForm(TimeForAMealForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->addFlash(
                'autocomplete_success',
                $this->generateEatingMessage(
                    $data['foods'],
                    $data['name']
                )
            );

            return $this->redirectToRoute('app_autocomplete');
        }

        return $this->render('ux_packages/autocomplete.html.twig', [
            'form' => $form,
        ]);
    }

    private function getDeliciousWord(): string
    {
        $words = ['delicious', 'scrumptious', 'mouth-watering', 'life-changing', 'world-beating', 'freshly-squeezed'];

        return $words[array_rand($words)];
    }

    private function generateEatingMessage(Collection $foods, string $name): string
    {
        $i = 0;
        $foodStrings = $foods->map(function (Food $food) use (&$i, $foods) {
            ++$i;
            $str = $food->getName();

            if ($i === \count($foods) && $i > 1) {
                $str = 'and ' . $str;
            }

            return $str;
        });

        return sprintf('Time for %s! Enjoy %s %s %s!',
            $name,
            \count($foodStrings) > 1 ? 'some' : 'a',
            $this->getDeliciousWord(),
            implode(\count($foodStrings) > 2 ? ', ' : ' ', $foodStrings->toArray())
        );
    }
}
