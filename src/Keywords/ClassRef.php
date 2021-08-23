<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;

class ClassRef
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<class_ref>');
    }

    public static function mustStart($tokens, $i)
    {
        return ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR);
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $classRef = ['classRef' => '\\"<name>"'];
        $repeatingClassRef = PatternParser::tokenize('"<repeating:classRef>"');
        $nameRepeatingClassRef = PatternParser::tokenize('"<name>""<repeating:classRef>"');

        if ($tToken[0] === T_NS_SEPARATOR) {
            $matches = Finder::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

            if (! $matches) {
                return false;
            }
            $startFrom = $matches[0];
            $placeholderValues[] = Finder::extractValue($matches[2][0]);
        } elseif ($tToken[0] === T_STRING) {
            $matches = Finder::compareTokens($nameRepeatingClassRef, $tokens, $startFrom, $classRef);
            if (! $matches) {
                $placeholderValues[] = $tToken;
            } else {
                $startFrom = $matches[0];
                $placeholderValues[] = Finder::extractValue($matches[2][0], $matches[1][0][1]);
            }
        } else {
            return false;
        }
    }
}
