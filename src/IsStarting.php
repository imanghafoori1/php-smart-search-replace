<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Keywords\Any;
use Imanghafoori\SearchReplace\Keywords\Boolean;
use Imanghafoori\SearchReplace\Keywords\ClassRef;
use Imanghafoori\SearchReplace\Keywords\Comment;
use Imanghafoori\SearchReplace\Keywords\FullClassRef;
use Imanghafoori\SearchReplace\Keywords\GlobalFunctionCall;
use Imanghafoori\SearchReplace\Keywords\Keyword;
use Imanghafoori\SearchReplace\Keywords\RepeatingPattern;
use Imanghafoori\SearchReplace\Keywords\Statement;
use Imanghafoori\SearchReplace\Keywords\WhiteSpace;

class IsStarting
{
    public static $keywords = [
        ClassRef::class,
        FullClassRef::class,
        GlobalFunctionCall::class,
        RepeatingPattern::class,
        Statement::class,
        Comment::class,
        Any::class,
        WhiteSpace::class,
        Boolean::class,
        Keyword::class
    ];

    public static function check($namedPatterns, $pToken, $tokens, $i)
    {
        foreach (self::$keywords as $keyword) {
            if ($keyword::is($pToken)) {
                return $keyword::mustStart($tokens, $i, $pToken, $namedPatterns);
            }
        }
    }
}
