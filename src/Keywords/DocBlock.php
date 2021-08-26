<?php

namespace Imanghafoori\SearchReplace\Keywords;

class DocBlock
{
    public static function is($string)
    {
        return $string === '<doc_block>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues) {
        $t = $tokens[$startFrom] ?? '_';

        if ($t[0] !== T_DOC_COMMENT) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
