<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Boolean
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, ['<bool>', '<boolean>']);
    }

    public static function mustStart($tokens, $i)
    {
        $t = $tokens[$i];

        return $t[0] === T_STRING && in_array(strtolower($t[1]), ['true', 'false'], true);
    }
}
