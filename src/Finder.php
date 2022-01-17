<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Keywords;
use Imanghafoori\TokenAnalyzer\Str;

class Finder
{
    public static $primitiveTokens = [
        Keywords\GlobalFunctionCall::class,
        Keywords\Any::class,
        Keywords\Variable::class,
        Keywords\Number::class,
        Keywords\Str::class,
        Keywords\Name::class,
        Keywords\Integer::class,
        Keywords\FloatNum::class,
        Keywords\DocBlock::class,
        Keywords\WhiteSpace::class,
        Keywords\Comment::class,
        Keywords\Boolean::class,
    ];

    public static $keywords = [
        Keywords\Any::class,
        Keywords\Variable::class,
        Keywords\Number::class,
        Keywords\Integer::class,
        Keywords\FloatNum::class,
        Keywords\Str::class,
        Keywords\Name::class,
        Keywords\DocBlock::class,
        Keywords\WhiteSpace::class,
        Keywords\Comment::class,
        Keywords\MethodVisibility::class,
        Keywords\Boolean::class,
        Keywords\Cast::class,
        Keywords\FullClassRef::class,
        Keywords\ClassRef::class,
        Keywords\GlobalFunctionCall::class,
        Keywords\InBetween::class,
        Keywords\Statement::class,
        Keywords\Comparison::class,
        Keywords\Until::class,
    ];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    public static function compareTokens($pattern, $tokens, $startFrom, $namedPatterns = [])
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $repeating = $placeholderValues = [];

        $pToken = $pattern[$j];

        while ($startFrom < $tCount && $j < $pCount) {
            if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && $pToken[1][1] === '<') {
                $trimmed = trim($pToken[1], '\'\"?');
                if (Finder::startsWith($trimmed, '<repeating:')) {
                    if (false === Keywords\RepeatingPattern::getValue($startFrom, $repeating, $tokens, $pToken, $namedPatterns)) {
                        return false;
                    }
                } elseif (false === self::checkKeywords($startFrom, $placeholderValues, $trimmed, $tokens, $pToken, $pattern, $pi, $j)) {
                    return false;
                }
            } elseif (! Finder::areTheSame($pToken, $tokens[$startFrom])) {
                return false;
            }

            [$pToken, $j] = self::getNextToken($pattern, $j);

            $pi = $startFrom;
            [, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
        }

        if ($pCount === $j) {
            return [$pi, $placeholderValues, $repeating,];
        }

        return false;
    }

    private static function compareOptionalTokens($patternTokens, $tokens, $startFrom)
    {
        $init = $startFrom;
        $pCount = count($patternTokens);
        $j = $pCount - 1;
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $patternTokens[$j];

        while ($tToken && $j !== -1) {
            foreach (self::$primitiveTokens as $class_token) {
                $trimmed = trim($pToken[1], '\'\"?');
                if ($class_token::is($trimmed)) {
                    $pToken[1] = $trimmed;
                    if ($class_token::getValue($tokens, $startFrom, $placeholderValues, $pToken) === false) {
                        $placeholderValues[] = [T_WHITESPACE, ''];
                    } else {
                        $startFrom--;
                    }
                    break;
                }
            }
            $j--;

            if (! isset($patternTokens[$j])) {
                return [array_reverse($placeholderValues), $init - $startFrom];
            }
            $pToken = $patternTokens[$j];
            $tToken = $tokens[$startFrom];
        }
    }

    public static function getNextToken($tokens, $i, $notIgnored = null)
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

    public static function is($token, $keyword)
    {
        return in_array(trim($token[1], '\'\"?'), (array) $keyword, true);
    }

    public static function isOptional($token)
    {

        return self::endsWith(trim(is_string($token) ? $token : $token[1], '\'\"'), '?');
    }

    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    public static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    public static function areTheSame($pToken, $token)
    {
        if ($pToken[0] !== $token[0]) {
            return false;
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

    public static function getMatches(
        $patternTokens,
        $tokens,
        $predicate = null,
        $mutator = null,
        $namedPatterns = [],
        $filters = [],
        $startFrom = 1
    ) {
        $pIndex = PatternParser::firstNonOptionalPlaceholder($patternTokens);
        $optionalStartingTokens = array_slice($patternTokens, 0, $pIndex);

        $matches = [];
        $i = $startFrom;
        $allCount = count($tokens);

        while ($i < $allCount) {
            $restPatternTokens = array_slice($patternTokens, $pIndex);
            $isMatch = self::compareTokens($restPatternTokens, $tokens, $i, $namedPatterns);
            if (! $isMatch) {
                $i++;
                continue;
            }

            [$optionalPatternMatchCount, $matched_optional_values] = self::optionalStartingTokens($optionalStartingTokens, $tokens, $i);

            [$end, $matchedValues, $repeatings] = $isMatch;
            $matchedValues = array_merge($matched_optional_values, $matchedValues);
            $data = ['start' => $i - $pIndex, 'end' => $end, 'values' => $matchedValues, 'repeatings' => $repeatings];
            if (Filters::apply($filters, $data, $tokens)) {
                if (! $predicate || call_user_func($predicate, $data, $tokens)) {
                    $mutator && $matchedValues = call_user_func($mutator, $matchedValues);
                    $matches[] = ['start' => $i - $optionalPatternMatchCount, 'end' => $end, 'values' => $matchedValues, 'repeatings' => $repeatings];
                }
            }

            $end > $i && $i = $end - 1; // fast-forward
            $i++;
        }

        return $matches;
    }

    public static function compareIt($tToken, int $type, $token, &$i)
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
        if (isset($pToken[1]) && self::is($pToken, '<white_space>')) {
            return self::getNextToken($tokens, $startFrom, T_WHITESPACE);
        } elseif (isset($pToken[1]) && self::is($pToken, '<comment>')) {
            return self::getNextToken($tokens, $startFrom, T_COMMENT);
        } else {
            return self::getNextToken($tokens, $startFrom);
        }
    }

    public static function matchesAny($avoidResultIn, $newTokens)
    {
        foreach ($avoidResultIn as $pattern) {
            $_matchedValues = Finder::getMatches(PatternParser::tokenize($pattern), $newTokens);
            if ($_matchedValues) {
                return true;
            }
        }

        return false;
    }

    public static function isRepeatingPattern($pToken)
    {
        if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && self::startsWith($pName = trim($pToken[1], '\'\"'), '<repeating:')) {
            return rtrim(Str::replaceFirst('<repeating:', '', $pName), '>');
        }
    }

    public static function getPortion($start, $end, $tokens)
    {
        $output = '';
        for ($i = $start - 1; $i < $end; $i++) {
            $output .= $tokens[$i][1] ?? $tokens[$i][0];
        }

        return $output;
    }

    private static function optionalStartingTokens($optionalStartingTokens, $tokens, $i)
    {
        if (! $optionalStartingTokens) {
            return [0, []];
        }

        [$matchedValues, $optionalMatchCount] = self::compareOptionalTokens($optionalStartingTokens, $tokens, $i - 1);

        return [$optionalMatchCount, $matchedValues];
    }

    public static function extractValue($matches, $first = '')
    {
        $segments = [$first];
        
        foreach ($matches as $match) {
            $segments[] = $match[0][1];
        }

        return [T_STRING, implode('\\', $segments), $match[0][2]];
    }

    private static function checkKeywords(&$startFrom, &$placeholderValues, $trimmed, $tokens, $pToken, $pattern, $pi, $j)
    {
        foreach (self::$keywords as $class_token) {
            if ($class_token::is($trimmed)) {
                if ($class_token::getValue($tokens, $startFrom, $placeholderValues, $pToken, $pattern, $pi, $j) === false) {
                    return false;
                } else {
                    break;
                }
            }
        }
    }
}
