<?php

namespace Imanghafoori\SearchReplace\Tokens;

use Imanghafoori\SearchReplace\TokenCompare;

class Statement {
    public static function is ($pToken) 
    {
        return TokenCompare::is($pToken, '<statement>');
    }

    public static function mustStart ($tToken, $repeatingClassRef, $tokens, $classRef, &$startFrom, &$placeholderValues) 
    {
        [$_value, $startFrom] = TokenCompare::readExpression($startFrom, $tokens);
        $placeholderValues[] = $_value;
    }
}