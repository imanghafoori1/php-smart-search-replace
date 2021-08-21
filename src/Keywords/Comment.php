<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Comment
{
    public static function Condition($pToken)
    {
        return TokenCompare::is($pToken, '<comment>');
    }

    public static function Body($tokens, $i)
    {
        return $tokens[$i][0] === T_COMMENT;
    }
}
