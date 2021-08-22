<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\TokenCompare;

class Statement
{
    public static function is($pToken)
    {
        return TokenCompare::is($pToken, '<statement>');
    }

    public static function mustStart()
    {
        return true;
    }
}
