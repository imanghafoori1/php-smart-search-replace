<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Keyword
{
    public static function Condition()
    {
        return true;
    }

    public static function Body($tokens, $i, $pToken)
    {
        $token = $tokens[$i];
        $isStartPoint = true;
        if (! TokenCompare::areTheSame($pToken, $token)) {
            $isStartPoint = false;
        }

        return $isStartPoint;

    }
}
