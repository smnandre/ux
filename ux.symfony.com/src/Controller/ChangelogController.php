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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChangelogController extends AbstractController
{
    public function __construct(
        private readonly ChangelogProvider $changeLogProvider,
    ) {
    }

    #[Route('/changelog', name: 'app_changelog')]
    public function __invoke(): Response
    {
        $changelog = $this->changeLogProvider->getChangelog();

        return $this->render('changelog.html.twig', [
            'changelog' => $changelog,
        ]);
    }

    #[Route('/changelog/{version}', name: 'app_changelog_version')]
    public function version(string $version): Response
    {
        $changelog = $this->changeLogProvider->getChangelog();

        $changelogVersion = $changelog[$version] ?? null;

        $versions = array_keys($changelog);
        $current = array_search($version, $versions);
        $previous = $versions[$current + 1] ?? null;
        $next = $versions[$current - 1] ?? null;

        return $this->render('changelog/version.html.twig', [
            'changelog' => $changelog,
            'version' => $version,
            'changelog_version' => $changelogVersion,
            'previous' => $previous,
            'next' => $next,
        ]);
    }
}
