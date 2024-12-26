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
use Symfony\Component\HttpFoundation\Request;
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
    public function tag(Request $request): Response
    {
        $changelog = $this->changeLogProvider->getChangelog();
        
        foreach ($changelog as $version) {
            if ($version['version'] === $request->get('version')) {
                break;
            }
            $version = null;
        }
        
        if (!isset($version)) {
            throw $this->createNotFoundException('Version not found');
        }
        
        if ($turboFrame = $request->headers->has('Turbo-Frame')) {
            return $this->renderBlock('changelog/changelog_version.html.twig', 'version', [
                'version' => $version,
            ]);
        }
        
        return $this->render('changelog/changelog_version.html.twig', [
            'version' => $version,
            'changelog' => $changelog,
        ]);
    }
}
