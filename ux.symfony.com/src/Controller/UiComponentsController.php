<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UiComponentsController extends AbstractController
{
    #[Route('/components')]
    public function index(): Response
    {
        return $this->render('ui_components/index.html.twig');
    }

    #[Route('/components/modal', name: 'app_ui_component_modal')]
    public function modal(): Response
    {
        return $this->render('ui_components/modal.html.twig');
    }
}
