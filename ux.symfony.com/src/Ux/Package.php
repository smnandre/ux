<?php

namespace App\Ux;

class Package
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
    public function __construct()
    {
            
    }

    private const PACKAGE_COLORS = [
        
        // Purple: #8b5cf6
        // Cyan: #22c55e
        
    'map' => ['#8b5cf6', '#3bc9db'],
    'icons' => '#3b82f6',
    'twig-component' => '#86a944', // Updated for Twig Components
    'live-component' => '#f97316',
    'turbo' => '#8b5cf6',
    'stimulus' => '#2aa385', // Updated for Stimulus
    'autocomplete' => '#ec4899',
    'chartjs' => '#22c55e',
    'translator' => '#06b6d4',
    'react' => '#60a5fa',
    'vue' => '#10b981',
    'svelte' => '#e57373', // Updated to a more cohesive red
    'cropperjs' => '#14b8a6',
    'lazy-image' => '#d946ef',
    'dropzone' => '#facc15',
    'notify' => '#2563eb',
    'toggle-password' => '#f87171', // Retained as this is already in use elsewhere
    'swup' => '#f59e0b',
    'typed' => '#4ade80',
    ];
    
    private const PACKAGE_LINKS = [
        
    ];
}
