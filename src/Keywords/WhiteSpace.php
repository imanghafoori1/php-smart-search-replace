<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class WhiteSpace
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<white_space>');
    }

    public static function mustStart($tokens, $i)
    {
        return $tokens[$i][0] === T_WHITESPACE;
    }
}
