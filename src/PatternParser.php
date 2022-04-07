<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\TokenAnalyzer\Str;

class PatternParser
{
    private static function getParams($pToken, $id)
    {
        $pName = trim($pToken[1], '\'\"');

        return rtrim(Str::replaceFirst("<$id:", '', $pName), '>');
    }

    public static function parsePatterns($patterns, $normalize = true)
    {
        $defaults = [
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'avoid_syntax_errors' => false,
            'post_replace' => [],
        ];

        $analyzedPatterns = [];
        [$prePattern, $prePattern2] = self::getSearchPatterns();
        $all = [
            // the order of the patterns matter.
            ['search' => $prePattern2, 'replace' => '"<"<1>">?"',],
            ['search' => $prePattern, 'replace' => '"<"<1>">"',],
        ];

        foreach ($patterns as $to) {
            if ($normalize) {
                $search = self::addQuotes($to['search'], $all);

                is_string($to['replace']) && ($to['replace'] = self::addQuotes(
                    $to['replace'],
                    [['search' => '<"<int>">', 'replace' => '"<"<1>">"',]]
                ));
            }

            [$tokens, $addedFilters] = self::extracted($search ?? $to['search']);
            $tokens = ['search' => $tokens] + $to + $defaults;
            foreach ($addedFilters as $addedFilter) {
                $tokens['filters'][$addedFilter[0]]['in_array'] = $addedFilter[1];
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

    private static function extracted($search)
    {
        $addedFilters = [];
        $tokens = self::tokenize($search);
        $count = 0;
        foreach ($tokens as $i => $pToken) {
            if ($pToken[0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }

            // If is placeholder "<like_this>"
            if ($pToken[1][1] === '<' && '>' === $pToken[1][strlen($pToken[1]) - 2]) {
                $count++;
            } else {
                continue;
            }

            $ids = self::getPlaceholderIds();
            foreach ($ids as [$id, $mutator]) {
                if (Finder::startsWith(trim($pToken[1], '\'\"'), "<$id:")) {
                    $tokens[$i][1] = "'<$id>'";
                    $readParams = self::getParams($pToken, $id);
                    $mutator && $readParams = $mutator($readParams);
                    $addedFilters[] = [$count, $readParams];
                }
            }
        }

        return [$tokens, $addedFilters];
    }

    private static function getPlaceholderIds(): array
    {
        $ids = [
            [
                'global_func_call',
                function ($values) {
                    $values = $u = explode(',', $values);
                    foreach ($u as $val) {
                        $values[] = '\\'.$val;
                    }

                    return $values;
                },
            ],
            ['name', null],
        ];

        return $ids;
    }

    private static function stringifyKeywords(string $search, string $prePattern, string $prePattern2)
    {

    }

    private static function getSearchPatterns()
    {
        $names = implode(',', [
            'white_space',
            'string',
            'str',
            'variable',
            'var',
            'statement',
            'in_between',
            'any',
            'cast',
            'number',
            'int',
            'integer',
            'doc_block',
            'name',
            'visibility',
            'float',
            'comment',
            'until',
            'full_class_ref',
            'class_ref',
            'bool',
            'boolean',
        ]);
        $prePattern = '<"<name:'.$names.'>">';
        $prePattern2 = '<"<name:'.$names.'>">?';

        return [$prePattern, $prePattern2];
    }

    private static function getReplacePatterns()
    {
        $names = implode(',', [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ]);
        $prePattern = '<"<int>">';
        $prePattern2 = '<"<int>"?>';

        return [$prePattern, $prePattern2];
    }

    private static function addQuotes(string $search, array $all)
    {
        [$tokens,] = Searcher::searchParsed($all, token_get_all('<?php '.$search));
        unset($tokens[0]);

        return Stringify::fromTokens($tokens);
    }
}
