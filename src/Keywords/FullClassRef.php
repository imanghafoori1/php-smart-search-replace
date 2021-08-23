<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\Finder;

class FullClassRef
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<full_class_ref>');
    }

    public static function mustStart($tokens, $i)
    {
        return $tokens[$i][0] === T_NS_SEPARATOR;
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $classRef = ['classRef' => '\\"<name>"'];
        $repeatingClassRef = PatternParser::tokenize('"<repeating:classRef>"');

        if ($tToken[0] !== T_NS_SEPARATOR) {
            return false;
        }

        $isMatch = Finder::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

        if (! $isMatch) {
            return false;
        }

        $placeholderValues[] = Finder::extractValue($isMatch[2][0]);
        $startFrom = $isMatch[0];
    }
}
