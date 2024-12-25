<?php

namespace Symfony\UX\Html\Attribute\Aria;

/**
 * https://www.w3.org/TR/wai-aria-1.2/#propcharacteristic_value
 * 
 * https://www.w3.org/TR/wai-aria-1.1/#typemapping
 */
enum ValueType: string
{
    case TrueFalse = 'true/false';
    case TriState = 'tristate';
    case TrueFalseUndefined = 'true/false/undefined';
    case IdReference = 'ID reference';
    case IdReferenceList = 'ID reference list';
    case Integer = 'integer';
    case Number = 'number';
    case String = 'string';
    case Token = 'token';
    case TokenList = 'token list';
    
    // Type Mapping -> HTML 
    // https://www.w3.org/TR/wai-aria-1.1/#typemapping
    
    
    
    
    public function getDefaultValue(): mixed
    {
        return match ($this) {
            self::TrueFalse => false,
            self::TriState => 'undefined',
            self::TrueFalseUndefined => 'undefined',
            self::IdReference => null,
            self::IdReferenceList => [],
            self::Integer => 0,
            self::Number => 0.0,
            self::String => '',
            self::Token => '',
            self::TokenList => [],
        };
    }
}

