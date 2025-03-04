<?php

namespace Imanghafoori\SearchReplace;

class PatternParser
{
    public static function parsePatterns($patterns, $normalize = true)
    {
        $defaults = [
            'ignore_whitespaces' => true,
            'predicate' => null,
            'mutator' => null,
            'named_patterns' => [],
            'filters' => [],
            'avoid_syntax_errors' => false,
            'post_replace' => [],
        ];

        $analyzedPatterns = [];

        $all = self::getSearchPatterns();

        foreach ($patterns as $to) {
            $normalize && $to = PatternParser\Normalizer::normalize($to, $all);

            [$tokens, $addedFilters] = PatternParser\Filters::extractFilter($to['search']);
            $tokens = ['search' => $tokens] + $to + $defaults;
            foreach ($addedFilters as $addedFilter) {
                $tokens['filters'][$addedFilter[0]]['in_array'] = $addedFilter[1];
            }
            $analyzedPatterns[] = $tokens;
        }

        return $analyzedPatterns;
    }

    private static function getSearchPatterns()
    {
        $names = implode(',', [
            'white_space',
            'not_whitespace',
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

        return [
            // the order of the patterns matter.
            ['search' => '<"<name:'.$names.'>">?', 'replace' => '"<"<1>">?"',],
            ['search' => '<"<name:'.$names.'>">', 'replace' => '"<"<1>">"',],
        ];
    }
}
