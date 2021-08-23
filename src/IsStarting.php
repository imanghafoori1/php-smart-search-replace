<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Keywords;

class IsStarting
{
    public static $keywords = [
        Keywords\ClassRef::class,
        Keywords\FullClassRef::class,
        Keywords\GlobalFunctionCall::class,
        Keywords\RepeatingPattern::class,
        Keywords\Statement::class,
        Keywords\Comment::class,
        Keywords\Any::class,
        Keywords\WhiteSpace::class,
        Keywords\Boolean::class,
        Keywords\Keyword::class
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
