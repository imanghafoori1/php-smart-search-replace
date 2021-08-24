<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Finder;

class DocBlock
{
    public static function is($pToken)
    {
        return Finder::is($pToken, '<doc_block>');
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues) {
        $t = $tokens[$startFrom] ?? '_';

        if ($t[0] !== T_DOC_COMMENT) {
            return false;
        }

        $placeholderValues[] = $t;
    }
}
