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

#[Route('/{package}', name: 'app_package')]
class UxPackageController extends AbstractController
{
    public function __construct(
        private readonly UxPackageRepository $packageRepository,
        private readonly ChangelogProvider $changeLogProvider,
        private readonly PackagistDataProvider $packagistData,
    )
    {
    }

    #[Route('/changelog', name: 'app_package_changelog')]
    public function changelog(string $package): Response
    {
        $uxPackage = $this->packageRepository->find($package) ?? throw $this->createNotFoundException(sprintf('Package "%s" not found', $package));

        $packageLog = $this->changeLogProvider->getPackageChangelog('ux-'.$uxPackage->getName());
        $packageData = $this->packagistData->getPackageData($uxPackage->getComposerName());

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

    #[Route('/requirements', name: 'app_package_requirements')]
    public function requirements(string $package): Response
    {
        $uxPackage = $this->packageRepository->find($package) ?? throw $this->createNotFoundException(sprintf('Package "%s" not found', $package));

        $packageData = $this->packagistData->getPackageData($uxPackage->getComposerName());

        $packageVersions = $packageData['versions'] ?? [];
        $currentVersion = array_shift($packageVersions);

        return $this->render('ux_packages/package_requirements.html.twig', [
            'package' => $uxPackage,
            'requirements' => [
                'requires' => $currentVersion['require'] ?? [],
                'devRequires' => $currentVersion['require-dev'] ?? [],
            ],
        ]);
    }
}
