<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\PatternParser;

class GlobalFunctionCall
{
    public static function is($string)
    {
        return $string === '<global_func_call>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        if (! self::mustStart($tokens, $startFrom)) {
            return false;
        }

        $tToken = $tokens[$startFrom] ?? '_';

        if ($tToken[0] === T_NS_SEPARATOR) {
            $matches = Finder::compareTokens(PatternParser::tokenize('\\"<name>"'), $tokens, $startFrom);
            if (! $matches) {
                return false;
            }

            $strValue = self::concatinate($matches[1]);
            $startFrom = $matches[0];
            $placeholderValues[] = $strValue;
        } elseif ($tToken[0] === T_STRING) {
            $placeholderValues[] = $tToken;
        } elseif (defined('T_NAME_QUALIFIED') && $tToken[0] === T_NAME_FULLY_QUALIFIED) {
            $placeholderValues[] = [T_STRING, $tToken[1], $tToken[2]];
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
