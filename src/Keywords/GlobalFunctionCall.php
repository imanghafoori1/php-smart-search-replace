<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\TokenAnalyzer\Str;

class GlobalFunctionCall
{
    public static function is($string)
    {
        return Finder::startsWith($string, '<global_func_call:');
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues, $pToken)
    {
        if (! self::mustStart($tokens, $startFrom)) {
            return false;
        }

        $tToken = $tokens[$startFrom] ?? '_';
        $patternNames = explode(',', self::getParams($pToken));

        if ($tToken[0] === T_NS_SEPARATOR) {
            $matches = Finder::compareTokens(PatternParser::tokenize('\\"<name>"'), $tokens, $startFrom);
            if (! $matches) {
                return false;
            }

            $strValue = self::concatinate($matches[1]);

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

    private static function concatinate(array $matches)
    {
        $segments = [''];
        foreach ($matches as $match) {
            $segments[] = $match[1];
        }

        return [T_STRING, implode('\\', $segments), $match[2]];
    }

    private static function getParams($pToken)
    {
        $pName = trim($pToken[1], '\'\"');

        return rtrim(Str::replaceFirst('<global_func_call:', '', $pName), '>');
    }

    private static function getPrevToken($tokens, $i)
    {
        $i--;
        $token = $tokens[$i] ?? '_';
        while ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT) {
            $i--;
            $token = $tokens[$i];
        }

        return [$token, $i];
    }

    private static function mustStart($tokens, $i)
    {
        [$prev, $prevI] = self::getPrevToken($tokens, $i);

        if ($prev[0] === T_NS_SEPARATOR) {
            [$prev] = self::getPrevToken($tokens, $prevI);
        }

        $excluded = [T_NEW, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION];
        defined('T_NULLSAFE_OBJECT_OPERATOR') && $excluded[] = T_NULLSAFE_OBJECT_OPERATOR;

        if (in_array($prev[0], $excluded)) {
            return false;
        }

        return true;
    }
}
