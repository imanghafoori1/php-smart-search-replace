<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\TokenCompare;

class FullClassRef
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<full_class_ref>');
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        $tToken = $tokens[$startFrom] ?? '_';
        $classRef = ['classRef' => '\\"<name>"'];
        $repeatingClassRef = PatternParser::tokenize('"<repeating:classRef>"');

        if ($tToken[0] !== T_NS_SEPARATOR) {
            return false;
        }

        $isMatch = TokenCompare::compareTokens($repeatingClassRef, $tokens, $startFrom, $classRef);

        if (! $isMatch) {
            return false;
        }

        $placeholderValues[] = TokenCompare::extractValue($isMatch[2][0]);
        $startFrom = $isMatch[0];
    }
}