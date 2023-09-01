<?php

namespace App\Controller\UxPackage;

use App\Form\DropzoneForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DropzoneController extends AbstractController
{
    #[Route('/dropzone', name: 'app_dropzone')]
    public function dropzone(Request $request): Response
    {
        $form = $this->createForm(DropzoneForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('dropzone_success', 'File uploaded! Then immediately discarded... since this is a demo server.');

            return $this->redirectToRoute('app_dropzone');
        }

        return $this->render('ux_packages/dropzone.html.twig', [
            'form' => $form,
        ]);
    }
}
