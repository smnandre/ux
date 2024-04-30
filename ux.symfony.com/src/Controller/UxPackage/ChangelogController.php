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

use App\Model\UxPackage;
use App\Service\Changelog\ChangelogProvider;
use App\Service\UxPackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChangelogController extends AbstractController
{
    public function __construct(private readonly ChangelogProvider $changelogProvider)
    {
    }

    #[Route('/{ux_package}/changelog/{version}', name: 'app_ux_package_changelog')]
    public function changelog(UxPackage $package, ?string $version = null): Response
    {
        $changelog = $this->changelogProvider->getUxPackageChangelog($package);
        $changelog = $this->parseChangelog($changelog);

        return $this->render('ux_packages/changelog.html.twig', [
            'package' => $package,
            'changelog' => $changelog,
            'version' => $version,
            'versions' => array_keys($changelog),
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
