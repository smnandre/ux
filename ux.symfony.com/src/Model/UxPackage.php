<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

class UxPackage
{
    private const array PACKAGE_COLORS = [
        'map' => ['#8b5cf6', '#3bc9db'],
        'icons' => '#3b82f6',
        'twig-component' => '#A1C94E', // Updated for Twig Components
        'live-component' => '#f59e0b',
        'turbo' => '#8b5cf6',
        'stimulus' => '#2aa385', // Updated for Stimulus
        'autocomplete' => '#ec4899',
        'chartjs' => '#22c55e',
        'translator' => '#06b6d4',
        'react' => '#60a5fa',
        'vue' => '#10b981',
        'svelte' => '#e57373', // Updated to a more cohesive red
        'cropperjs' => '#06b6d4',
        'lazy-image' => '#d946ef',
        'dropzone' => '#facc15',
        'notify' => '#2563eb',
        'toggle-password' => '#f87171', // Retained as this is already in use elsewhere
        'swup' => '#EA9633',
        'typed' => '#4ade80',
    ];
    
    
    // --color-light: hsl(from var(--color-base) h 140 83)
    // --color-light: hsl(from var(--color-base) calc(h - 5) 140 83)
    
    private array $colors =
            [
            'icons' => ['#0ff', '#f0f'],
            'map' => ['#1BA980', '#C0CB2A', '#1BA980'],
            'twig-component' => ['#7FA020', '#A1C94E'],
            'live-component' => ['#D98A11', '#BF5421', '#D98A11'],
            'turbo' => ['#5920A0', '#844EC9'],
            'stimulus' => ['#2EB17B', '#3D9A89', '#2EB17B'],
            'autocomplete' => ['#DF275E', '#E85995'],
            'translator' => ['#2248D0', '#00FFB2'],
            'chartjs' => ['#21A81E', '#45DD42'],
            'react' => ['#10A2CB', '#42caf0'],
            'vue' => ['#35b67c', '#8CE3BC'],
            'svelte' => ['#FF3E00', '#BE3030'],
            'cropperjs' => ['#1E8FA8', '#3FC0DC'],
            'lazy-image' => ['#AC2777', '#F246AD'],
            'dropzone' => ['#AC9F27', '#E8D210'],
            'swup' => ['#D87036', '#EA9633'],
            'notify' => ['#204CA0', '#3D82EA'],
            'toggle-password' => ['#BE0404', '#FD963C'],
            'typed' => ['#20A091', '#4EC9B3'],
        ];
    
    public function getColorBase(): string
    {
        if (is_array($colorBase = self::PACKAGE_COLORS[$this->name])) {
            return $colorBase[0];
        }
        
        return $colorBase;
    }
    
    public function getColor0(): string
    {
        return $this->colors[$this->name][0];
    }
    
    public function getColor1(): string
    {
        return $this->colors[$this->name][2]?? false ? $this->colors[$this->name][1] : $this->colors[$this->name][0];
    }
    
    public function getColor2(): string
    {
        return $this->colors[$this->name][2] ?? $this->colors[$this->name][1];
    }
    
    private ?string $docsLink = null;
    private ?string $docsLinkText = null;
    private ?string $screencastLink = null;
    private ?string $screencastLinkText = null;
    
    public string $color2;

    public function __construct(
        private string $name,
        private string $humanName,
        private string $route,
        private string $color,
        private string $gradient,
        private string $tagLine,
        private string $description,
        private ?string $createString = null,
        private ?string $imageFileName = null,
        private ?string $composerName = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHumanName(): string
    {
        return $this->humanName;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getGradient(): string
    {
        return $this->gradient;
    }

    public function getImageFilename(?string $format = null): string
    {
        return $this->imageFileName ?? $this->name.($format ? '-'.$format : '').'.png';
    }

    public function getTagLine(): string
    {
        return $this->tagLine;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getComposerName(): string
    {
        return $this->composerName ?? 'symfony/ux-'.$this->getName();
    }

    public function getComposerRequireCommand(): string
    {
        return 'composer require '.$this->getComposerName();
    }

    public function getDocsLink(): ?string
    {
        return $this->docsLink;
    }

    public function setDocsLink(string $url, string $description): self
    {
        $this->docsLink = $url;
        $this->docsLinkText = $description;

        return $this;
    }

    public function getScreencastLink(): ?string
    {
        return $this->screencastLink;
    }

    public function setScreencastLink(string $url, string $description): self
    {
        $this->screencastLink = $url;
        $this->screencastLinkText = $description;

        return $this;
    }

    public function getDocsLinkText(): ?string
    {
        return $this->docsLinkText;
    }

    public function getScreencastLinkText(): ?string
    {
        return $this->screencastLinkText;
    }

    public function setOfficialDocsUrl(string $officialDocsUrl): self
    {
        $this->officialDocsUrl = $officialDocsUrl;

        return $this;
    }

    private string $officialDocsUrl;

    public function getOfficialDocsUrl(): string
    {
        return $this->officialDocsUrl ??= \sprintf('https://symfony.com/bundles/ux-%s/current/index.html', $this->name);
    }

    public function getCreateString(): ?string
    {
        return $this->createString;
    }

    public function getSocialImage(?string $format = null): string
    {
        return 'images/ux_packages/'.$this->name.($format ? ('-'.$format) : '').'.png';
    }

    public function getImage(?string $format = null): string
    {
        return 'images/ux_packages/'.$this->getImageFilename($format);
    }
}
