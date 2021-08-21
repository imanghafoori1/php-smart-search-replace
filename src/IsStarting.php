<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Keywords\ClassRef;
use Imanghafoori\SearchReplace\Keywords\FullClassRef;
use Imanghafoori\SearchReplace\Keywords\GlobalFunctionCall;
use Imanghafoori\SearchReplace\Keywords\Keyword;
use Imanghafoori\SearchReplace\Keywords\RepeatingPattern;
use Imanghafoori\SearchReplace\Keywords\Statement;

class IsStarting
{
    public static $keywords = [
        ClassRef::class,
        FullClassRef::class,
        GlobalFunctionCall::class,
        RepeatingPattern::class,
        Statement::class,
        Keyword::class
    ];

    public static function check($namedPatterns, $pToken, $tokens, $i)
    {
        foreach (self::$keywords as $keyword) {
            if ($keyword::condition($pToken)) {
                return $keyword::body($tokens, $i, $pToken, $namedPatterns);
            }
        }
    }
}
