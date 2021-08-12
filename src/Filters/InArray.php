<?php

namespace Imanghafoori\SearchReplace\Filters;

class InArray
{
    public static function check($placeholderVal, $parameter): bool
    {
        if (is_string($parameter)) {
            $parameter = explode(',', $parameter);
        }

        return in_array($placeholderVal[1] ?? $placeholderVal[0], $parameter);
    }
}
