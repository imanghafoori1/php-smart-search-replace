<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;
use Imanghafoori\TokenAnalyzer\Str;

class GlobalFunctionCall
{
    public static function is($pToken)
    {
        return self::isGlobalFuncCall($pToken);
    }

    public static function isGlobalFuncCall($pToken)
    {
        if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && TokenCompare::startsWith($pName = trim($pToken[1], '\'\"'), '<global_func_call:')) {
            return rtrim(Str::replaceFirst('<global_func_call:', '', $pName), '>');
        }
    }

    public static function mustStart($tokens, $i)
    {
        $token = $tokens[$i];

        if ($token[0] !== T_STRING && $token[0] !== T_NS_SEPARATOR) {
            return false;
        }
        $excluded = [T_NEW, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION];
        defined('T_NULLSAFE_OBJECT_OPERATOR') && $excluded[] = T_NULLSAFE_OBJECT_OPERATOR;

        [$prev, $prevI] = self::getPrevToken($tokens, $i);
        if ($prev[0] === T_NS_SEPARATOR) {
            [$prev] = self::getPrevToken($tokens, $prevI);
        }
        if (in_array($prev[0], $excluded)) {
            return false;
        }

        return true;
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
}
