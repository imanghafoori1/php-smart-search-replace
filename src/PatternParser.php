<?php

namespace Imanghafoori\SearchReplace;

class PatternParser
{
    private static function replaceTokens($tokens, $from, $to, string $with)
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

    public static function parsePatterns($patterns)
    {
        $analyzedPatterns = [];
        $defaults = [
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'post_replace' => []
        ];
        $i = 0;
        foreach ($patterns as $pattern => $to) {
            is_string($to) && $to = ['replace' => $to];
            $analyzedPatterns[$i] = ['search' => self::analyzePatternTokens($pattern)] + $to + $defaults;
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

    public static function analyzePatternTokens($pattern)
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

    public static function applyAllMatches($patternMatches, $replace, $tokens, $namedPatterns)
    {
        $replacementLines = [];
        foreach ($patternMatches as $matchValue) {
            [$tokens, $lineNum] = self::applyMatch($replace, $matchValue, $tokens, [], [], $namedPatterns);
            $replacementLines[] = $lineNum;
        }

        return [$tokens, $replacementLines];
    }

    public static function applyMatch($replace, $match, $tokens, $avoiding = [], $postReplaces = [], $namedPatterns = [])
    {
        $newValue = self::applyWithPostReplacements($replace, $match['values'], $postReplaces, $namedPatterns, $match['repeatings']);

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
        if (is_callable($replace)) {
            return call_user_func($replace, $values);
        }

        $newValue = $replace;
        foreach ($values as $number => $value) {
            !is_array($value[0]) && $newValue = str_replace(['"<'.($number + 1).'>"', "'<".($number + 1).">'"], $value[1] ?? $value[0], $newValue);
        }

        return $newValue;
    }

    public static function applyWithPostReplacements($replace, $values, $postReplaces, $namedPatterns = [], $repeating = [])
    {
        $newValue = self::applyOnReplacements($replace, $values);

        [$newTokens,] = PostReplace::applyPostReplaces($postReplaces, token_get_all('<?php '.$newValue));
        array_shift($newTokens);

        foreach ($newTokens as $index => $t) {
            $r = TokenCompare::isRepeatingPattern($t);
            if (!$r) {
                continue;
            }
            [$num, $pName] = explode(':', $r);
            $pattern = $namedPatterns[$pName];

            foreach ($repeating as $repeat) {
                $repeatsValues = [];
                foreach ($repeat as $r) {
                    $repeatsValues[] = self::applyOnReplacements($pattern, $r);
                }
                $newTokens[$index][1] = implode('', $repeatsValues);
            }
        }

        return Stringify::fromTokens($newTokens);
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
