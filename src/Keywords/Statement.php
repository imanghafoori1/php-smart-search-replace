<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Statement
{
    public static function Condition($pToken)
    {
        return TokenCompare::is($pToken, '<statement>');
    }

    public static function Body()
    {
        return true;
    }
}
