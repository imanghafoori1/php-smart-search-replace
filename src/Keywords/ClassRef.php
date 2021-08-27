<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;

class ClassRef
{
    public static function is($string)
    {
        return $string === '<class_ref>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $classRef = ['classRef' => '\\"<name>"'];

        if ($tToken[0] === T_NS_SEPARATOR) {
            $repeatingClassRef = PatternParser::tokenize('"<repeating:classRef>"');
            $matches = Finder::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

            if (! $matches) {
                return false;
            }
            $startFrom = $matches[0];
            $placeholderValues[] = Finder::extractValue($matches[2][0]);
        } elseif ($tToken[0] === T_STRING) {
            $repeatingPattern = PatternParser::tokenize('"<name>""<repeating:classRef>"');
            $matches = Finder::compareTokens($repeatingPattern, $tokens, $startFrom, $classRef);
            if (! $matches) {
                $placeholderValues[] = $tToken;
            } else {
                $startFrom = $matches[0];
                $placeholderValues[] = Finder::extractValue($matches[2][0], $matches[1][0][1]);
            }
        } elseif (defined('T_NAME_QUALIFIED') && ($tToken[0] === T_NAME_QUALIFIED || $tToken[0] === T_NAME_FULLY_QUALIFIED)) {
            $placeholderValues[] = [T_STRING, $tToken[1], $tToken[2]];
        } else {
            return false;
        }
    }
}
