<?php

namespace Imanghafoori\SearchReplace;

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

    private static function compareOptionalTokens($patternTokens, $tokens, $startFrom)
    {
        $init = $startFrom;
        $pCount = count($patternTokens);
        $j = $pCount - 1;
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $patternTokens[$j];

        while ($tToken && $j !== -1) {
            foreach (self::$primitiveTokens as $classToken) {
                $trimmed = trim($pToken[1], '\'\"?');
                if ($classToken::is($trimmed)) {
                    $pToken[1] = $trimmed;
                    if ($classToken::getValue($tokens, $startFrom, $placeholderValues, $pToken) === false) {
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
        $startFrom = 1,
        $maxMatch = null,
        $ignoreWhitespace = true
    ) {
        $pIndex = PatternParser::firstNonOptionalPlaceholder($patternTokens);
        $optionalStartingTokens = array_slice($patternTokens, 0, $pIndex);

        $matches = [];
        $matchesCount = 0;
        $i = $startFrom;
        $allCount = count($tokens);

        while ($i < $allCount) {
            $restPatternTokens = array_slice($patternTokens, $pIndex);
            $isMatch = Tokens::compareTokens($restPatternTokens, $tokens, $i, $namedPatterns, $ignoreWhitespace);
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
                    $matchesCount++;
                    $matches[] = ['start' => $i - $optionalPatternMatchCount, 'end' => $end, 'values' => $matchedValues, 'repeatings' => $repeatings];
                }
            }

            $end > $i && $i = $end - 1; // fast-forward
            $i++;
            if ($maxMatch && $matchesCount === $maxMatch) {
                return $matches;
            }
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
}
