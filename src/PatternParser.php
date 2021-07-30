<?php

namespace Imanghafoori\SearchReplace;

class PatternParser
{
    public static function replaceTokens($tokens, $from, $to, string $with)
    {
        $lineNumber = 0;

        for ($i = $from; $i <= $to; $i++) {
            if ($i === $from) {
                $lineNumber = $tokens[$i][2] ?? 0;
                $tokens[$i] = [T_STRING, $with, 1];
                continue;
            }

            if ($i > $from && $i <= $to) {
                ! $lineNumber && ($lineNumber = $tokens[$i][2] ?? 0);
                $tokens[$i] = [T_STRING, '', 1];
            }
        }

        return [$tokens, $lineNumber];
    }

    public static function parsePatterns($refactorPatterns)
    {
        $tokens_to_search_for = [];

        $i = 0;
        foreach ($refactorPatterns as $pattern => $to) {
            $tokens_to_search_for[$i] = ['search' => self::analyzeTokens($pattern)] + $to + ['predicate' => null, 'mutator' => null, 'refiners' => []];
            $i++;
        }

        return $tokens_to_search_for;
    }

    public static function search($patterns, $tokens)
    {
        return self::findMatches((self::parsePatterns($patterns)), $tokens);
    }

    public static function searchReplace($patterns, $tokens)
    {
        [$tokens, $replacementLines] = self::search($patterns, $tokens);

        return [Stringify::fromTokens($tokens), $replacementLines];
    }

    public static function findMatches($patterns, $tokens)
    {
        $replacementLines = $matches = [];

        foreach ($patterns as $pIndex => $pattern) {
            $matches[$pIndex] = TokenCompare::getMatch($pattern['search'], $tokens, $pattern['predicate'], $pattern['mutator']);
            [$tokens, $replacementLines] = self::applyPattern($matches[$pIndex], $pattern['replace'], $tokens, $replacementLines);
            $pattern['refiners'] && [$tokens,] = self::search($pattern['refiners'], token_get_all(Stringify::fromTokens($tokens)));

        }

        return [$tokens, $replacementLines];
    }

    private static function isPlaceHolder($token)
    {
        if ($token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }
        $map = [
            "'<string>'" => T_CONSTANT_ENCAPSED_STRING,
            "'<str>'" => T_CONSTANT_ENCAPSED_STRING,
            "'<variable>'" => T_VARIABLE,
            "'<var>'" => T_VARIABLE,
            "'<number>'" => T_LNUMBER,
            "'<name>'" => T_STRING,
            "'<boolean>'" => T_STRING,
            "'<bool>'" => T_STRING,
        ];

        return $map[$token[1]] ?? false;
    }

    public static function applyPatterns($patterns, $matches, $tokens)
    {
        $replacePatterns = array_values($patterns);

        $replacementLines = [];
        foreach ($matches as $pi => $patternMatch) {
            [$tokens, $replacementLines] = self::applyPattern($patternMatch, $replacePatterns[$pi]['replace'], $tokens, $replacementLines);
        }

        return [$tokens, $replacementLines];
    }

    private static function analyzeTokens($pattern)
    {
        $tokens = token_get_all('<?php '.$pattern);
        array_shift($tokens);

        foreach ($tokens as $i => $token) {
            // transform placeholders
            if ($placeHolder = self::isPlaceHolder($token)) {
                $tokens[$i] = [$placeHolder, null];
            }
        }

        return $tokens;
    }

    private static function applyPattern($patternMatch, $replace, $tokens, array $replacementLines): array
    {
        foreach ($patternMatch as $match) {
            $newValue = $replace;
            foreach ($match['values'] as $number => $value) {
                $newValue = str_replace(['"<'.($number + 1).'>"', "'<".($number + 1).">'"], $value[1], $newValue);
            }
            [$tokens, $lineNum] = self::replaceTokens($tokens, $match['start'], $match['end'], $newValue);
            $replacementLines[] = $lineNum;
        }

        return [$tokens, $replacementLines];
    }
}
