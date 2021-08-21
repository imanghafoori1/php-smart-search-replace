<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;


class ClassRef
{
    static function Condition($pToken)
    {
        return TokenCompare::is($pToken, '<class_ref>');
    }

    static function Body($tokens, $i)
    {
        return ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR);
    }
}
