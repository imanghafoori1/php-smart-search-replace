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
            $tokens_to_search_for[$i] = ['search' => self::analyzeTokens($pattern)] + $to + ['predicate' => null, 'mutator' => null, 'post_replace' => []];
            $i++;
        }

        return $tokens_to_search_for;
    }

    public static function search($patterns, $tokens)
    {
        return self::findMultiplePatterns(self::parsePatterns($patterns), $tokens);
    }

    public static function searchReplace($patterns, $tokens)
    {
        [$tokens, $replacementLines] = self::search($patterns, $tokens);

        return [Stringify::fromTokens($tokens), $replacementLines];
    }

    public static function findMultiplePatterns($patterns, $tokens)
    {
        $replacementLines = [];

        foreach ($patterns as $pattern) {
            $m = TokenCompare::getMatches($pattern['search'], $tokens, $pattern['predicate'], $pattern['mutator']);
            [$tokens, $replacementLines] = self::applyAllMatches($m, $pattern['replace'], $tokens, $replacementLines);

            if ($pattern['post_replace']) {
                foreach ($pattern['post_replace'] as $key => $postReplace) {
                    [$tokens,] = self::search([$key => $postReplace], token_get_all(Stringify::fromTokens($tokens)));
                }
            }

        }

        return [$tokens, $replacementLines];
    }

    public static function findPatternMatches($pattern, $tokens)
    {
        $replacementLines = [];

        $result = TokenCompare::getMatches($pattern['search'], $tokens, $pattern['predicate'], $pattern['mutator']);
        [$tokens, $replacementLines] = self::applyAllMatches($result, $pattern['replace'], $tokens, $replacementLines);

        if ($pattern['post_replace']) {
            foreach ($pattern['post_replace'] as $key => $postReplace) {
                [$tokens,] = self::search([$key => $postReplace], token_get_all(Stringify::fromTokens($tokens)));
            }
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
            "'<,>'" => ',',
        ];

        return $map[$token[1]] ?? false;
    }

    public static function applyPatterns($patterns, $matches, $tokens)
    {
        $replacePatterns = array_values($patterns);

        $replacementLines = [];
        foreach ($matches as $pi => $patternMatch) {
            [$tokens, $replacementLines] = self::applyAllMatches($patternMatch, $replacePatterns[$pi]['replace'], $tokens, $replacementLines);
        }

        return [$tokens, $replacementLines];
    }

    public static function analyzeTokens($pattern)
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

    private static function applyAllMatches($patternMatch, $replace, $tokens, array $replacementLines)
    {
        foreach ($patternMatch as $match) {
            [$tokens, $lineNum] = self::applyMatch($replace, $match, $tokens);
            $replacementLines[] = $lineNum;
        }

        return [$tokens, $replacementLines];
    }

    public static function applyMatch($replace, $match, $tokens)
    {
        $newValue = $replace;
        foreach ($match['values'] as $number => $value) {
            $newValue = str_replace(['"<'.($number + 1).'>"', "'<".($number + 1).">'"], $value[1] ?? $value[0], $newValue);
        }
        [$tokens, $lineNum] = self::replaceTokens($tokens, $match['start'], $match['end'], $newValue);

        return [$tokens, $lineNum];
    }
}
