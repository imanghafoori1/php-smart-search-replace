<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\TokenAnalyzer\Str;

class PatternParser
{
    private static function getParams($pToken)
    {
        $pName = trim($pToken[1], '\'\"');

        return rtrim(Str::replaceFirst('<global_func_call:', '', $pName), '>');
    }

    public static function parsePatterns($patterns)
    {
        $defaults = [
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'avoid_syntax_errors' => false,
            'post_replace' => [],
        ];

        $addedFilters = [];
        $analyzedPatterns = [];
        foreach ($patterns as $to) {
            $tokens = self::tokenize($to['search']);
            $count = 0;
            foreach ($tokens as $i => $pToken) {
                if ($pToken[0] !== T_CONSTANT_ENCAPSED_STRING) {
                    continue;
                }

                if ($pToken[1][1] === '<' && '>' === $pToken[1][strlen($pToken[1]) - 2]) {
                    $count++;
                } else {
                    continue;
                }
                if (Finder::startsWith(trim($pToken[1], '\'\"'), '<global_func_call:')) {
                    $tokens[$i][1] = "'<global_func_call>'";
                    $addedFilters[] = [$count, self::getParams($pToken)];
                }
            }
            $tokens = ['search' => $tokens] + $to + $defaults;
            foreach ($addedFilters as $addedFilter) {
                $values = $u = explode(',', $addedFilter[1]);
                foreach ($u as $val) {
                    $values[] = '\\'.$val;
                }

                $tokens['filters'][$addedFilter[0]]['in_array'] = $values;
            }
            $analyzedPatterns[] = $tokens;
        }

        return $analyzedPatterns;
    }

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

    public static function firstNonOptionalPlaceholder($patternTokens)
    {
        $i = 0;
        foreach ($patternTokens as $i => $pt) {
            if (! self::isOptionalPlaceholder($pt)) {
                return $i;
            }
        }

        return $i;
    }

    private static function isOptionalPlaceholder($token)
    {
        if ($token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        return Finder::endsWith($token[1], '>?"') || Finder::endsWith($token[1], ">?'");
    }
}
