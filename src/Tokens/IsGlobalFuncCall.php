<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;
use Imanghafoori\SearchReplace\PatternParser;

class IsGlobalFuncCall {
    public static function is ($pToken) 
    {
        return TokenCompare::isGlobalFuncCall($pToken);
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues, $nameRepeatingClassRef, $pattern, $pi, $j, $pToken) {
        $patternNames = explode(',', TokenCompare::isGlobalFuncCall($pToken));

        if ($tToken[0] === T_NS_SEPARATOR) {
            $matches = TokenCompare::compareTokens(PatternParser::tokenize('\\"<name>"'), $tokens, $startFrom);
            if (! $matches) {
                return false;
            }

            $strValue = TokenCompare::concatinate($matches[1]);

            foreach ($patternNames as $patternName23) {
                if ($strValue[1] === $patternName23 || $strValue[1] === '\\'.$patternName23) {
                    $startFrom = $matches[0];
                    $placeholderValues[] = $strValue;
                    break;
                }
            }
        } elseif ($tToken[0] === T_STRING) {
            if (! in_array($tToken[1], $patternNames)) {
                return false;
            }

            $placeholderValues[] = $tToken;

        } else {
            return false;
        }
    }
}