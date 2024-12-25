<?php

namespace Symfony\UX\Html\Attribute;

class Attributes
{
    public static function isState(string $attribute): bool {
        return in_array(self::trimPrefix($attribute), self::STATES, true);
    }
    
    public static function isProperty(string $attribute): bool {
        return in_array(self::trimPrefix($attribute), self::PROPERTIES, true);
    }
    
    public static function trimPrefix(string $attribute): string {
        return str_starts_with($attribute, 'aria-') ? substr($attribute, 5) : $attribute;
    }
    
    public static function withPrefix(string $attribute): string {
        return str_starts_with($attribute, 'aria-') ? $attribute : 'aria-' . $attribute;
    }
    
    private const array STATES = [
       'busy' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['div', 'span', 'button', 'input', 'select', 'textarea'],
        ],
        'checked' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['input'],
        ],
        'current' => [
            'default' => 'page', // Possible values: 'page', 'step', 'location', 'date', 'time', 'true'
            'type' => 'token list',
            'elements' => ['a', 'button', 'div', 'span', 'li', 'menuitem', 'option'],
        ],
        'disabled' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['button', 'input', 'select', 'textarea', 'a', 'div', 'span'],
        ],
        'expanded' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['button', 'div', 'span', 'li', 'menuitem', 'option', 'a'],
        ],
        'grabbed' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['div', 'span'],
        ],
        'modal' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['div', 'span'],
        ],
        'multiline' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['input', 'textarea'],
        ],
        'multiselectable' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['listbox', 'grid', 'treegrid'],
        ],
        'pressed' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['button'],
        ],
        'readonly' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['input', 'textarea'],
        ],
        'required' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['input', 'select', 'textarea'],
        ],
        'selected' => [
            'default' => false,
            'type' => 'boolean',
            'elements' => ['option', 'li', 'menuitem'],
        ],
    ];

    private const array PROPERTIES = [
        'activedescendant',
        'atomic',
        'autocomplete',
        'colcount',
        'colindex',
        'colspan',
        'controls',
        'describedby',
        'details',
        'dropeffect',
        'errormessage',
        'flowto',
        'haspopup',
        'hidden',
        'invalid',
        'keyshortcuts',
        'label',
        'labelledby',
        'level',
        'live',
        'orientation',
        'owns',
        'placeholder',
        'posinset',
        'roledescription',
        'rowcount',
        'rowindex',
        'rowspan',
        'setsize',
        'sort',
        'valuemax',
        'valuemin',
        'valuenow',
        'valuetext',
    ];
}
