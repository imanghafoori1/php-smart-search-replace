<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;


class ClassRef
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<class_ref>');
    }

    public static function mustStart($tokens, $i)
    {
        return ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR);
    }
}
