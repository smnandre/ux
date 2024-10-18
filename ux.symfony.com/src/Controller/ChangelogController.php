<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Service\Changelog\ChangelogProvider;
use App\Service\Packagist\PackagistDataProvider;
use App\Service\UxPackageRepository;
use App\Twig\Components\ChangelogItem;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChangelogController extends AbstractController
{
    public function __construct(
        private readonly ChangelogProvider $changeLogProvider,
    )
    {
    }

    #[Route('/changelog', name: 'app_changelog')]
    public function __invoke(): Response
    {
        $changelog = $this->changeLogProvider->getChangelog();

        return $this->render('changelog.html.twig', [
            'changelog' => $changelog,
        ]);
    }

    #[Route('/{package}/changelog', name: 'app_changelog_package')]
    public function package(string $package, UxPackageRepository $packageRepository, PackagistDataProvider $packagistData): Response
    {
        $uxPackage = $packageRepository->find($package);

        $packageLog = $this->changeLogProvider->getPackageChangelog('ux-'.$uxPackage->getName());
        $packageData = $packagistData->getPackageData($uxPackage->getComposerName());

        $firstLine = array_shift($packageLog);

        $changelog = [];
        foreach ($packageLog as $a) {
            $lines = explode("\n", $a);
            $version = array_shift($lines);

            $versionData = $packageData['versions']['v'.$version] ?? [];
            if ([] === $versionData) {
                continue;
            }

            $changelog[] = [
                'body' => implode("\n", $lines),
                'version' => $version,
                'name' => 'v'.$version,
                'date' => $versionData['time'] ?? null,
            ];
        }

        return $this->render('ux_packages/package_changelog.html.twig', [
            'package' => $uxPackage,
            'changelog' => $changelog,
        ]);
    }
}
