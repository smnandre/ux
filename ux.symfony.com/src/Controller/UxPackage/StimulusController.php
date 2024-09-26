<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\UxPackage;

use App\Bridge\Packagist\PackagistDataProvider;
use App\Service\Changelog\ChangelogProvider;
use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StimulusController extends AbstractController
{
    public function __construct(
        private readonly UxPackageRepository $packageRepository,
    ) {
    }

    #[Route('/stimulus', name: 'app_stimulus')]
    public function __invoke(): Response
    {
        $package = $this->packageRepository->find('stimulus');

        return $this->render('ux_packages/stimulus.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/stimulus/install', name: 'app_stimulus_install')]
    public function documentation(PackagistDataProvider $packagistDataProvider): Response
    {
        $package = $this->packageRepository->find('stimulus');

        $packageData = $packagistDataProvider->getPackageData($package->getComposerName());
        dd($packageData);

        return $this->render('ux_packages/stimulus/install.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/stimulus/changelog', name: 'app_stimulus_changelog')]
    public function changelog(ChangelogProvider $changelogProvider): Response
    {
        $package = $this->packageRepository->find('live-component');

       $packageLog = $changelogProvider->getUxPackageChangelog($package);
       // $releases = $changelogProvider->getReleases(1, $package);
       // dump($packageLog, $releases);

        // $firstLine = array_shift($packageLog);
        // $changelog = [];
        // foreach ($packageLog as $a) {
        //     $lines = explode("\n", $a);
        //     $version = array_shift($lines);
        //
        //     $versionData = $packageData['versions']['v'.$version] ?? [];
        //     if ([] === $versionData) {
        //         continue;
        //     }
        //
        //     $changelog[] = [
        //         'body' => implode("\n", $lines),
        //         'version' => $version,
        //         'name' => 'v'.$version,
        //         'date' => $versionData['time'] ?? null,
        //     ];
        // }

         $changelog = $this->parseChangelog($packageLog);

         return $this->render('ux_packages/stimulus/changelog.html.twig', [
                         'changelog' => $changelog,
            'package' => $package,
          // 'version' => $version,
            'versions' => array_keys($changelog),
        ]);

        return $this->render('ux_packages/changelog.html.twig', [
            'package' => $package,
            'changelog' => $changelog,
            // 'version' => $version,
            // 'versions' => array_keys($changelog),
        ]);
    }

    private function parseChangelog(string $string): array
    {
        $changelog = [];

        $versions = explode("\n## ", $string);
        array_shift($versions);
        foreach ($versions as $version) {
            $tag = strtok($version, "\n");
            $changelog[$tag] = $version;
        }

        return $changelog;
    }

}
