<?php

namespace Imanghafoori\SearchReplace;

class PatternParser
{
    public static function parsePatterns($patterns)
    {
        $defaults = [
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'avoid_syntax_errors' => false,
            'post_replace' => [],
        ];

        $analyzedPatterns = [];
        foreach ($patterns as $to) {
            $analyzedPatterns[] = ['search' => self::tokenize($to['search'])] + $to + $defaults;
        }

        return $analyzedPatterns;
    }

    public static function tokenize($pattern)
    {
        $tokens = token_get_all('<?php '.self::cleanComments($pattern));
        array_shift($tokens);

        return $tokens;
    }

    private static function cleanComments($pattern)
    {
        foreach (['"', "'"] as $c) {
            for ($i = 1; $i !== 11; $i++) {
                $pattern = str_replace("$c<$i:", "$c<", $pattern, $count);
            }
        }

        return $pattern;
    }
}
