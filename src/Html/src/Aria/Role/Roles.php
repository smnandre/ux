<?php

namespace Symfony\UX\Html\Aria;

/**
 * https://www.w3.org/TR/wai-aria-1.2/#roles_categorization
 */
class Roles
{
    // https://www.w3.org/TR/wai-aria-1.2/#abstract_roles
    private const ABSTRACT_ROLES = [
        'command',
        'composite',
        'input',
        'landmark',
        'range',
        'roletype',
        'section',
        'sectionhead',
        'select',
        'structure',
        'widget',
        'window',
    ];
    
    // https://www.w3.org/TR/wai-aria-1.2/#widget_roles
    private const WIDGET_ROLES = [
        'button',
        'checkbox',
        'gridcell',
        'link',
        'menuitem',
        'menuitemcheckbox',
        'menuitemradio',
        'option',
        'progressbar',
        'radio',
        'scrollbar',
        'searchbox',
        'separator',
        'slider',
        'spinbutton',
        'switch',
        'tab',
        'tabpanel',
        'textbox',
        'treeitem',
        // Composite roles
        'combobox',
        'grid',
        'listbox',
        'menu',
        'menubar',
        'radiogroup',
        'tablist',
        'tree',
        'treegrid',
    ];
    
    // https://www.w3.org/TR/wai-aria-1.2/#document_structure_roles
    
    
    // https://www.w3.org/TR/wai-aria-1.2/#landmark_roles
    private const LANDMARK_ROLES = [
        'banner',
        'complementary',
        'contentinfo',
        'form',
        'main',
        'navigation',
        'region',
        'search',
    ];
    
    private const LIVE_REGION_ROLES = [
        'alert',
        'log',
        'marquee',
        'status',
        'timer',
    ];
    
    private const WINDOW_ROLES = [
        'alertdialog',
        'dialog',
    ];
}
