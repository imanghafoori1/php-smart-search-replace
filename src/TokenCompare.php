<?php

namespace Imanghafoori\SearchReplace;

class TokenCompare
{
    private static $placeHolders = [T_CONSTANT_ENCAPSED_STRING, T_VARIABLE, T_LNUMBER, T_STRING];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    private static function compareTokens($pattern, $tokens, $startFrom, $namedPatterns = [])
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $pattern[$j];

        while ($startFrom < $tCount && $j < $pCount) {
            if ($namedPatterns && $patternName = self::isRepeatingPattern($pToken)) {
                $pattern = $namedPatterns[$patternName];
                $ranalyzedPattern = PatternParser::analyzeTokens($pattern);
                $rpToken = $ranalyzedPattern[0];
                if ($r = self::compareTokens($ranalyzedPattern, $tokens, $startFrom)) {
                    [$repeatingMatches, $startFrom] = self::findRepeatingMatches($startFrom, $tokens, $rpToken, $ranalyzedPattern, $j, null, null);
                    $placeholderValues[] = $repeatingMatches;
                } else {
                    return false;
                }
            } elseif (self::is($pToken, '<until>')) {
                $untilTokens = [];
                $line = 1;
                for ($k = $pi + 1; $tokens[$k] !== $pattern[$j + 1]; $k++) {
                    ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
                    $untilTokens[] = $tokens[$k];
                }
                $startFrom = $k - 1;
                $placeholderValues[] = [T_STRING, Stringify::fromTokens($untilTokens), $line];
            } elseif (self::is($pToken, '<until_match>')) {
                $startingToken = $pattern[$j - 1]; // may use getPreviousToken()
                if (! in_array($startingToken, ['(', '[', '{'], true)) {
                    throw new \Exception('pattern invalid');
                }

                $anti = self::getAnti($startingToken);

                if ($anti !== $pattern[$j + 1]) {
                    throw new \Exception('pattern invalid');
                }

                $untilTokens = [];
                $line = 1;
                $level = 0;
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
    private static function compareOptionalTokens($patternTokens, $tokens, $startFrom)
    {
        $pCount = count($patternTokens);
        $j = $pCount - 1;
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $patternTokens[$j];

        while ($tToken && $j !== -1) {
            if (self::is($pToken, '<any>')) {
                $placeholderValues[] = $tToken;
                $startFrom--;
            } elseif (self::is($pToken, '<bool>') || self::is($pToken, '<boolean>')) {
                if ($tToken[0] === T_STRING && in_array(strtolower($tToken[1]), ['true', 'false'])) {
                    $placeholderValues[] = $tToken;
                    $startFrom--;
                } else {
                    $placeholderValues[] = [T_WHITESPACE, ''];
                }
            } else {
                $name = trim($pToken[1], '\'\"?');
                $map = [
                    "<white_space>" => T_WHITESPACE,
                    "<comment>" => T_COMMENT,
                    "<string>" => T_CONSTANT_ENCAPSED_STRING,
                    "<str>" => T_CONSTANT_ENCAPSED_STRING,
                    "<variable>" => T_VARIABLE,
                    "<var>" => T_VARIABLE,
                    "<number>" => T_LNUMBER,
                    "<name>" => T_STRING,
                    "<,>" => ',',
                ];
                $type = $map[$name];

                if ($tToken[0] === $type) {
                    $placeholderValues[] = $tToken;
                    $startFrom--;
                } else {
                    $placeholderValues[] = [T_WHITESPACE, ''];
                }
            }
            $j--;

            //[$pToken, $j] = self::getNextToken($patternTokens, $j);
            //$pi = $startFrom;
            if (!isset($patternTokens[$j])) {
                return array_reverse($placeholderValues);
            }
            $pToken = $patternTokens[$j];
            $tToken = $tokens[$startFrom];
            //[$tToken, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
        }
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

    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    private static function getAnti(string $startingToken)
    {
        return [
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ][$startingToken];
    }

    private static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
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

    public static function getMatches($patternTokens, $tokens, $predicate = null, $mutator = null, $namedPatterns = [], $startFrom = 0)
    {
        $pIndex = self::firstNonOptionalPlaceholder($patternTokens);
        $pToken = $patternTokens[$pIndex];
        $optionalStartingTokens = array_slice($patternTokens, 0, $pIndex);

        return self::getMatchesSimplePatterns($startFrom, $tokens, $pToken, $patternTokens, $pIndex, $optionalStartingTokens, $predicate, $mutator, $namedPatterns);
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

    private static function findRepeatingMatches($startFrom, $tokens, $startingPatternToken, array $analyzedPattern, $pIndex, $predicate, $mutator)
    {
        $repeatingMatches = [];
        $end = $startFrom;
        while (true) {
            $matches = self::getMatchesSimplePatterns($startFrom, $tokens, $startingPatternToken, $analyzedPattern, 0, [], null, null, [],1);

            if (! $matches) {
                break;
            }

            $end = $matches[0]['end'];
            [, $startFrom] = self::getNextToken($tokens, $end);
            $repeatingMatches[] = $matches[0];
        }

        return [$repeatingMatches, $end];
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
    private static function getMatchesSimplePatterns($startFrom, $tokens, $pToken, $patternTokens, $pIndex, $optionalStartingTokens, $predicate, $mutator, $namedPatterns = [], $maxDepth = 1000)
    {
        $matches = [];
        $i = $startFrom;
        $allCount = count($tokens);

        while ($i < $allCount) {
            if ($namedPatterns && $patternName = self::isRepeatingPattern($pToken)) {
                $pattern = $namedPatterns[$patternName];
                $ranalyzedPattern = PatternParser::analyzeTokens($pattern);
                $rpToken = $ranalyzedPattern[0];
                [$repeatingMatches, $i] = self::findRepeatingMatches($i, $tokens, $rpToken, $ranalyzedPattern, $pIndex, $predicate, $mutator);
                $pIndex++;

                // run out of pattern tokens
                if (! isset($patternTokens[$pIndex])) {
                    return $repeatingMatches;
                }

                $pToken = $patternTokens[$pIndex];
            }
            $token = $tokens[$i];

            if (! self::areTheSame($pToken, $token)) {
                $i++;
                continue;
            }

            $optionalPatternMatchCount = 0;
            if ($optionalStartingTokens) {
                $matchedValues1 = self::compareOptionalTokens($optionalStartingTokens, $tokens, $i - 1);
                foreach ($matchedValues1 as $x) {
                    if ($x !== [T_WHITESPACE, '']) {
                        $optionalPatternMatchCount++;
                    }
                }
            } else {
                $matchedValues1 = [];
            }

            $restPatternTokens = array_slice($patternTokens, $pIndex);
            $isMatch = self::compareTokens($restPatternTokens, $tokens, $i, $namedPatterns);
            if (! $isMatch) {
                $i++;
                continue;
            }

            [$k, $matchedValues] = $isMatch;
            $matchedValues = array_merge($matchedValues1, $matchedValues);
            $data = ['start' => $i - $pIndex, 'end' => $k, 'values' => $matchedValues];
            if (! $predicate || $predicate($data, $tokens)) {
                $mutator && $matchedValues = $mutator($matchedValues);
                $matches[] = ['start' => $i - $optionalPatternMatchCount, 'end' => $k, 'values' => $matchedValues];
                if (count($matches)=== $maxDepth) {
                    return $matches;
                }
            }

            $k > $i && $i = $k - 1; // fast-forward
            $i++;
        }

        return $matches;
    }

    private static function isRepeatingPattern($pToken)
    {
        if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && self::startsWith($str = trim($pToken[1], '\'\"'), '<repeating:')) {
            return rtrim(ltrim($str, '<repeating:'), '>');
        }
    }

    public static function isOptionalPlaceholder($token)
    {
        if ($token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        $optionals = [
            "<any>?",
            "<white_space>?",
            "<comment>?",
            "<string>?",
            "<str>?",
            "<variable>?",
            "<var>?",
            "<number>?",
            "<num>?",
            "<name>?",
            "<boolean>?",
            "<bool>?",
            "<,>?",
        ];

        $name = trim($token[1], '\"\'');

        return in_array($name, $optionals, true);
    }

    public static function getPortion($start, $end, $tokens)
    {
        $output = '';
        for ($i = $start - 1; $i < $end; $i++) {
            $output .= $tokens[$i][1] ?? $tokens[$i][0];
        }

        return $output;
    }

    private static function firstNonOptionalPlaceholder($patternTokens)
    {
        $i = 0;
        foreach ($patternTokens as $i => $pt) {
            if (! self::isOptionalPlaceholder($pt)) {
                return $i;
            }
        }

        return $i;
    }
}
