<?php

namespace Imanghafoori\SearchReplace\Filters;

class SameName
{
    public static function check($placeholderVal, $parameter, $tokens, $placeholderVals)
    {
        return $placeholderVals[$parameter - 1][1] === $placeholderVal[1];
    }
}
