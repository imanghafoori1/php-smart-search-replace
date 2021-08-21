<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\TokenCompare;
use Imanghafoori\TokenAnalyzer\Str;

class RepeatingPattern
{
    public static function Condition($pToken)
    {
        return $pToken[0] === T_CONSTANT_ENCAPSED_STRING && TokenCompare::startsWith(trim($pToken[1], '\'\"'), '<repeating:');
    }

    public static function Body($tokens, $i, $pToken, $namedPatterns)
    {
        $isStartPoint = true;
        TokenCompare::startsWith($pName = trim($pToken[1], '\'\"'), '<repeating:');
        $patternName = rtrim(Str::replaceFirst('<repeating:', '', $pName), '>');

        // We compare it like a normal pattern.
        if (! TokenCompare::compareTokens(PatternParser::tokenize($namedPatterns[$patternName]), $tokens, $i)) {
            $isStartPoint = false;
        }

        return $isStartPoint;
    }
}
