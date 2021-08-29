<?php

namespace Imanghafoori\SearchReplace\Keywords;

use Imanghafoori\SearchReplace\Stringify;

class Statement
{
    public static function is($string)
    {
        return $string === '<statement>';
    }

    public static function getValue($tokens, &$startFrom, &$placeholderValues)
    {
        [$_value, $startFrom] = self::readExpression($startFrom, $tokens);
        $placeholderValues[] = $_value;
    }

    private static function readExpression($i, $tokens)
    {
        $level = 0;
        $collected = [];
        $line = 1;

        for ($k = $i; true; $k++) {
            if (! isset($tokens[$k])){
                return ['', $k];
            }
            $nextToken = $tokens[$k];

            if (in_array($nextToken, [';', ',', ']'], true) && $level === 0) {
                $value = [T_STRING, Stringify::fromTokens($collected), $line];

                return [$value, $k - 1];
            }
            $collected[] = $nextToken;

            if (in_array($nextToken[0], ['[', '(', '{', T_CURLY_OPEN], true)) {
                $level++;
            }

            if (in_array($nextToken[0], [']', ')', '}'], true)) {
                $level--;
            }

            isset($nextToken[2]) && $line = $nextToken[2];
        }
    }
}
