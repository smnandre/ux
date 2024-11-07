<?php

namespace App\Ux;

class Packages
{
    private const string ICON = 'icon';
    private const string TWIG_COMPONENT = 'twig_component';
    private const string LIVE_COMPONENT = 'live_component';
    private const string TURBO = 'turbo';
    private const string STIMULUS = 'stimulus';
    private const string AUTOCOMPLETE = 'autocomplete';
    private const string TRANSLATOR = 'translator';
    private const string CHARTJS = 'chartjs';
    private const string REACT = 'react';
    
    public static function getPackages(): array
    {
        return [
            self::ICON => 'icon',
            self::TWIG_COMPONENT => 'twig_component',
            self::LIVE_COMPONENT => 'live_component',
            self::TURBO => 'turbo',
            self::STIMULUS => 'stimulus',
            self::AUTOCOMPLETE => 'autocomplete',
            self::TRANSLATOR => 'translator',
            self::CHARTJS => 'chartjs',
            self::REACT => 'react',
        ];
    }
    
    private const PACKAGE_COLORS = [
        
    ];
    
    private const PACKAGE_LINKS = [
        
    ];
}
