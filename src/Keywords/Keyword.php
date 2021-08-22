<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Keyword
{
    public static function is()
    {
        return true;
    }

    public static function mustStart($tokens, $i, $pToken)
    {
        $token = $tokens[$i];

        return TokenCompare::areTheSame($pToken, $token) ? true : false;
    }
}
