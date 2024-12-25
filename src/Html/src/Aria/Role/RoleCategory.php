<?php

namespace Symfony\UX\Html\Aria;

/**
 * https://www.w3.org/TR/wai-aria-1.2/#roles_categorization
 */
enum RoleCategory: string
{
    case Abstract = 'abstract';
    case Widget = 'widget';
    case DocumentStructure = 'document-structure';
    case Landmark = 'landmark';
    case LiveRegion = 'live-region';
    case Window = 'window';
    
    // https://www.w3.org/TR/wai-aria-1.2/#abstract_roles
    private const AbstractRoles = [
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
}
