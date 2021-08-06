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

        $j = 0;
        while ($lineNumber === 0 && $j < 5) {
            $j++;
            $lineNumber = $tokens[$i++][2] ?? 0;
        }

        return [$tokens, $lineNumber];
    }

    public static function parsePatterns($refactorPatterns)
    {
        $tokens_to_search_for = [];

        $i = 0;
        foreach ($refactorPatterns as $pattern => $to) {
            is_string($to) && $to = ['replace' => $to];
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
        $replacementAllLines = [];

        foreach ($patterns as $pattern) {
            [$tokens, $replacementLines] = self::findPatternMatches($pattern, $tokens);

            $replacementAllLines = array_merge($replacementAllLines, $replacementLines);
        }

        return [$tokens, $replacementAllLines];
    }

    public static function findPatternMatches($pattern, $tokens)
    {
        $result = TokenCompare::getMatches($pattern['search'], $tokens, $pattern['predicate'], $pattern['mutator']);

        [$tokens, $replacementLines] = self::applyAllMatches($result, $pattern['replace'], $tokens);

        isset($pattern['post_replace']) && [$tokens] = self::applyPostReplaces($pattern['post_replace'], $tokens);

        return [$tokens, $replacementLines];
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
        $nums = [
            "'<1:", "'<2:", "'<3:", "'<4:", "'<5:", "'<6:", "'<7:", "'<8:", "'<9:", "'<10:",
        ];
        $pattern = str_replace($nums, "'<", $pattern);

        $nums = [
            '"<1:', '"<2:', '"<3:', '"<4:', '"<5:', '"<6:', '"<7:', '"<8:', '"<9:', '"<10:',
        ];
        $pattern = str_replace($nums, '"<', $pattern);

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

    private static function applyAllMatches($patternMatches, $replace, $tokens)
    {
        $replacementLines = [];
        foreach ($patternMatches as $matchValue) {
            [$tokens, $lineNum] = self::applyMatch($replace, $matchValue, $tokens);
            $replacementLines[] = $lineNum;
        }

        return [$tokens, $replacementLines];
    }

    public static function applyMatch($replace, $match, $tokens, $avoiding = [], $postReplaces = [])
    {
        $newValue = self::applyWithPostReplacements($replace, $match['values'], $postReplaces);

        [$newTokens, $lineNum] = self::replaceTokens($tokens, $match['start'], $match['end'], $newValue);

        $wasPostReplaced = false;

        $hasAny = TokenCompare::matchesAny($avoiding, token_get_all(Stringify::fromTokens($newTokens)));

        if ($hasAny) {
            return [$tokens, null, $wasPostReplaced];
        }

        return [$newTokens, $lineNum, $wasPostReplaced];
    }

    public static function applyOnReplacements($replace, $values)
    {
        $newValue = $replace;
        foreach ($values as $number => $value) {
            $newValue = str_replace(['"<'.($number + 1).'>"', "'<".($number + 1).">'"], $value[1] ?? $value[0], $newValue);
        }

        return $newValue;
    }

    public static function applyPostReplaces($postReplaces, $tokens)
    {
        $wasReplaced = false;

        foreach ($postReplaces as $key => $postReplace) {
            [$tokens, $lines] = self::search([$key => $postReplace], token_get_all(Stringify::fromTokens($tokens)));
            $lines && $wasReplaced = true;
        }

        return [$tokens, $wasReplaced];
    }

    public static function applyWithPostReplacements($replace, $values, $postReplaces)
    {
        $newValue = self::applyOnReplacements($replace, $values);

        [$newTokens,] = PatternParser::applyPostReplaces($postReplaces, token_get_all('<?php '.$newValue));
        array_shift($newTokens);

        return Stringify::fromTokens($newTokens);
    }
}
