<?php

namespace Imanghafoori\SearchReplace;

class TokenCompare
{
    private static $placeHolders = [T_CONSTANT_ENCAPSED_STRING, T_VARIABLE, T_LNUMBER, T_STRING, ','];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    private static function compareTokens($pattern, $tokens, $startFrom)
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $pattern[$j];

        while ($startFrom < $tCount && $j < $pCount) {
            if (self::is($pToken, '<until>')) {
                $untilTokens = [];
                $line = 1;
                for ($k = $pi + 1; $tokens[$k] !== $pattern[$j + 1]; $k++) {
                    ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
                    $untilTokens[] = $tokens[$k];
                }
                $startFrom = $k - 1;
                $placeholderValues[] = [T_STRING, Stringify::fromTokens($untilTokens), $line];
            } elseif (self::is($pToken, '<until_match>')) {
                $untilTokens = [];
                $line = 1;
                $level = 0;
                $startingToken = ($pattern[$j - 1]); // may use getPreviousToken()
                if (! in_array($startingToken, ['(', '[', '{'], true)) {
                    throw new \Exception('pattern invalid');
                }

                $anti = self::getAnti($startingToken);

                if ($anti !== $pattern[$j + 1]) {
                    throw new \Exception('pattern invalid');
                }

                for ($k = $pi + 1; true; $k++) {
                    if ($tokens[$k] === $anti && $level === 0) {
                        break;
                    }

                    $tokens[$k] === $startingToken && $level--;
                    $tokens[$k] === $anti && $level++;

                    ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
                    $untilTokens[] = $tokens[$k];
                }

                $startFrom = $k - 1;
                $placeholderValues[] = [T_STRING, Stringify::fromTokens($untilTokens), $line];
            } elseif (self::is($pToken, '<any>')) {
                $placeholderValues[] = $tToken;
            } elseif (self::is($pToken, '<white_space>')) {
                $result = self::compareIt($tToken, T_WHITESPACE, $pToken[1], $startFrom);
                if ($result === null) {
                    return false;
                }
                $placeholderValues[] = $result;
            } elseif (self::is($pToken, '<comment>')) {
                $result = self::compareIt($tToken, T_COMMENT, $pToken[1], $startFrom);
                if ($result === null) {
                    return false;
                }
                $placeholderValues[] = $result;
            } else {
                $same = self::areTheSame($pToken, $tToken);

                if (! $same) {
                    return false;
                }

                $same === 'placeholder' && $placeholderValues[] = $tToken;
            }

            [$pToken, $j] = self::getNextToken($pattern, $j);

            $pi = $startFrom;
            [$tToken, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
        }

        if ($pCount === $j) {
            return [$pi, $placeholderValues];
        }

        return false;
    }

    private static function getNextToken($tokens, $i, $notIgnored = null)
    {
        $ignored = self::$ignored;

        if ($notIgnored) {
            unset($ignored[$notIgnored]);
        }

        $i++;
        $token = $tokens[$i] ?? '_';
        while (in_array($token[0], $ignored, true)) {
            $i++;
            $token = $tokens[$i] ?? [null, null];
        }

        return [$token, $i];
    }

    private static function is($token, $keyword)
    {
        return $token[0] === T_CONSTANT_ENCAPSED_STRING && trim($token[1], '\'\"?') === $keyword;
    }

    private static function isOptional($token)
    {
        return self::endsWith(trim($token, '\'\"'), '?');
    }

    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
    private static function getAnti(string $startingToken)
    {
        return [
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ][$startingToken];
    }

    private static function areTheSame($pToken, $token)
    {
        if (self::is($pToken, '<any>')) {
            return true;
        }

        if (self::is($pToken, '<white_space>')) {
            return $token[0] === T_WHITESPACE;
        }

        if (self::is($pToken, '<comment>')) {
            return $token[0] === T_COMMENT;
        }

        if ($pToken[0] !== $token[0]) {
            return false;
        }

        if (in_array($pToken[0], self::$placeHolders, true) && !isset($pToken[1])) {
            return 'placeholder';
        }

        if (! isset($pToken[1]) || ! isset($token[1])) {
            return true;
        }

        if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
            return trim($pToken[1], '\'\"') === trim($token[1], '\'\"');
        }

        if ($pToken[0] === T_STRING && (in_array(strtolower($pToken[1]), ['true', 'false', 'null'], true))) {
            return strtolower($pToken[1]) === strtolower($token[1]);
        }

        return $pToken[1] === $token[1];
    }

    public static function getMatches($patternTokens, $tokens, $predicate = null, $mutator = null)
    {
        $matches = [];

        $pToken = $patternTokens[0];
        $i = 0;
        $allCount = count($tokens);
        while ($i < $allCount) {
            $token = $tokens[$i];
            if (! self::areTheSame($pToken, $token)) {
                $i++;
                continue;
            }

            $isMatch = self::compareTokens($patternTokens, $tokens, $i);
            if (! $isMatch) {
                $i++;
                continue;
            }

            [$k, $matchedValues] = $isMatch;
            $data = ['start' => $i, 'end' => $k, 'values' => $matchedValues];
            if (! $predicate || $predicate($data, $tokens)) {
                $mutator && $matchedValues = $mutator($matchedValues);
                $matches[] = ['start' => $i, 'end' => $k, 'values' => $matchedValues];
            }

            $k > $i && $i = $k - 1; // fast-forward
            $i++;
        }

        return $matches;
    }

    private static function compareIt($tToken, int $type, $token, &$i)
    {
        if ($tToken[0] === $type) {
            return $tToken;
        }

        if (self::isOptional($token)) {
            $i--;

            return [T_WHITESPACE, ''];
        }
    }

    private static function forwardToNextToken($pToken, $tokens, $startFrom)
    {
        if (self::is($pToken, '<white_space>')) {
            return self::getNextToken($tokens, $startFrom, T_WHITESPACE);
        } elseif (self::is($pToken, '<comment>')) {
            return self::getNextToken($tokens, $startFrom, T_COMMENT);
        } else {
            return self::getNextToken($tokens, $startFrom);
        }
    }

    public static function matchesAny($avoidResultIn, $newTokens)
    {
        foreach ($avoidResultIn as $pattern) {
            $_matchedValues = TokenCompare::getMatches(PatternParser::analyzeTokens($pattern), $newTokens);
            if ($_matchedValues) {
                return true;
            }
        }

        return false;
    }

    public static function getPortion($start, $end, $tokens)
    {
        $output = '';
        for ($i = $start - 1; $i < $end; $i++) {
            $output .= $tokens[$i][1] ?? $tokens[$i][0];
        }

        return $output;
    }
}
