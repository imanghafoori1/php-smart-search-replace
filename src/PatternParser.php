<?php

namespace Imanghafoori\SearchReplace;

class PatternParser
{
    public static function parsePatterns($patterns)
    {
        $analyzedPatterns = [];
        $defaults = [
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'avoid_syntax_errors' => false,
            'post_replace' => [],
        ];
        $i = 0;
        foreach ($patterns as $to) {
            $analyzedPatterns[$i] = ['search' => self::tokenize($to['search'])] + $to + $defaults;
            $i++;
        }

        return $analyzedPatterns;
    }

    private static function isPlaceHolder($token)
    {
        if ($token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }
        $map = [
            "<string>" => T_CONSTANT_ENCAPSED_STRING,
            "<str>" => T_CONSTANT_ENCAPSED_STRING,
            "<variable>" => T_VARIABLE,
            "<var>" => T_VARIABLE,
            "<number>" => T_LNUMBER,
            "<name>" => T_STRING,
            "<boolean>" => T_STRING,
            "<bool>" => T_STRING,
            "<,>" => ',',
        ];

        return $map[trim($token[1], '\'\"')] ?? false;
    }

    public static function tokenize($pattern)
    {
        $tokens = token_get_all('<?php '.self::cleanComments($pattern));
        array_shift($tokens);

        foreach ($tokens as $i => $token) {
            // transform placeholders
            if ($placeHolder = self::isPlaceHolder($token)) {
                $tokens[$i] = [$placeHolder, null];
            }
        }

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
