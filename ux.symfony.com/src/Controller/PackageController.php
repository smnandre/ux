<?php

namespace App\Controller;

use App\Service\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PackageController extends AbstractController
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private Packages $assetPackages
    ) {
    }

    #[Route('/twig-component', name: 'app_twig_component')]
    public function twigComponent(): Response
    {
        return $this->render('ux_packages/twig-component.html.twig');
    }

    #[Route('/lazy-image', name: 'app_lazy_image')]
    public function lazyImage(): Response
    {
        $legosFilePath = $this->getParameter('kernel.project_dir').'/assets/images/legos.jpg';

        return $this->render('ux_packages/lazy-image.html.twig', [
            'legosFilePath' => $legosFilePath,
        ]);
    }

    #[Route('/react', name: 'app_react')]
    public function react(PackageRepository $packageRepository): Response
    {
        $packagesData = $this->getNormalizedPackages($packageRepository);

        return $this->render('ux_packages/react.html.twig', [
            'packagesData' => $packagesData,
        ]);
    }

    #[Route('/vue', name: 'app_vue')]
    public function vue(PackageRepository $packageRepository): Response
    {
        $packagesData = $this->getNormalizedPackages($packageRepository);

        return $this->render('ux_packages/vue.html.twig', [
            'packagesData' => $packagesData,
        ]);
    }

    #[Route('/svelte', name: 'app_svelte')]
    public function svelte(PackageRepository $packageRepository): Response
    {
        $packagesData = $this->getNormalizedPackages($packageRepository);

        return $this->render('ux_packages/svelte.html.twig', [
            'packagesData' => $packagesData,
        ]);
    }

    #[Route('/typed', name: 'app_typed')]
    public function typed(): Response
    {
        return $this->render('ux_packages/typed.html.twig');
    }

    #[Route('/translator', name: 'app_translator')]
    public function translator(): Response
    {
        return $this->render('ux_packages/translator.html.twig');
    }

    private function getNormalizedPackages(PackageRepository $packageRepository): array
    {
        $packagesData = $this->normalizer->normalize($packageRepository->findAll());
        $assetPackages = $this->assetPackages;

        return array_map(function (array $data) use ($assetPackages) {
            $data['url'] = $this->generateUrl($data['route']);
            unset($data['route']);
            $data['imageUrl'] = $assetPackages->getUrl('images/'.$data['imageFilename']);

            return $data;
        }, $packagesData);
    }
}
