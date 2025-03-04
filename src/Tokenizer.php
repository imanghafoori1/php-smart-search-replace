<?php

namespace Imanghafoori\SearchReplace;

class Tokenizer
{
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