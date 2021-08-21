<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Any
{
    public static function Condition($pToken)
    {
        return TokenCompare::is($pToken, '<any>');
    }

    public static function Body()
    {
        return true;
    }
}
