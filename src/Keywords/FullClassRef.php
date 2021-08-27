<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;

class FullClassRef
{
    public static function is($string)
    {
        return $string === '<full_class_ref>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $tToken = $tokens[$startFrom] ?? '_';

        if (defined('T_NAME_FULLY_QUALIFIED')) {
            if ($tToken[0] !== T_NAME_FULLY_QUALIFIED) {
                return false;
            }

            $placeholderValues[] = [T_STRING, $tToken[1], $tToken[2]];

            return;
        }

        if ($tToken[0] !== T_NS_SEPARATOR) {
            return false;
        }

        $absClassRef = ['classRef' => '\\"<name>"'];
        $repeatingClassRef = PatternParser::tokenize('"<repeating:classRef>"');

        $isMatch = Finder::compareTokens($repeatingClassRef, $tokens, $startFrom, $absClassRef);

        if (! $isMatch) {
            return false;
        }

        $placeholderValues[] = Finder::extractValue($isMatch[2][0]);
        $startFrom = $isMatch[0];
    }
}
