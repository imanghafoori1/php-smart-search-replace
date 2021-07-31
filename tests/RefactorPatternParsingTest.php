<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\TokenCompare;
use PHPUnit\Framework\TestCase;

class RefactorPatternParsingTest extends TestCase
{
    /** @test */
    public function match_comment()
    {
        $patterns = [
           '["<comment>"]' => [
                'replace' => '[]',
            ]
        ];

        $startFile = '<?php [/**/]; [1,]; ["s"];';
        $resultFile = '<?php []; [1,]; ["s"];';
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function match_white_space()
    {
        $patterns = [
           '["<white_space>"]' => [
                'replace' => '[]',
            ]
        ];

        $startFile = '<?php [/**/]; [1,]; ["s"]; [ ];';
        $resultFile = '<?php [/**/]; [1,]; ["s"]; [];';
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function post_replace()
    {
        $patterns = [
            "]" => [
                'replace' => ',]',
                'post_replace' => [
                    ',,]' => ['replace' => ',]'],
                    '[,]' => ['replace' => '[]'],
                ],
            ]
        ];

        $startFile = '<?php [1,2,3]; [1,2,3,]; [ ]; [/**/ ];';

        $resultFile = '<?php [1,2,3,]; [1,2,3,]; []; [];';
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function capturing_place_holders()
    {
        $patterns = [
            "if (!'<variable>' && '<boolean>') { return response()->'<name>'(['message' => __('<string>')], '<number>'); }" => ['replace' => 'Foo::bar("<1>", "<2>", "<3>"(), "<4>");'],
            'foo(false, true, null);' => ['replace' => 'bar("hi");'],
        ];
        $startFile = file_get_contents(__DIR__.'/stubs/SimplePostController.stub');
        $resultFile = file_get_contents(__DIR__.'/stubs/ResultSimplePostController.stub');
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([15, 23, 26, 27], $replacedAt);
    }

    /** @test */
    public function basic_capturing_place_holders()
    {
        $patterns = [
            "'<var>' = 1;" => ['replace' => "2 ==='<1>';"],
        ];
        $startFile = '<?php $var = 1;';
        $resultFile = '<?php 2 ===$var;';
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function can_parse_patterns()
    {
        $patterns = require __DIR__.'/stubs/refactor_patterns.php';
        $sampleFileTokens = token_get_all(file_get_contents(__DIR__.'/stubs/SimplePostController.stub'));

        $patterns = PatternParser::parsePatterns($patterns);
        foreach ($patterns as $pIndex => $pattern) {
            $matches[$pIndex] = TokenCompare::getMatch($pattern['search'], $sampleFileTokens, $pattern['predicate'], $pattern['mutator']);
        }

        $this->assertEquals($matches[0][0]['values'],
            [
                [T_VARIABLE, '$user', 15],
                [T_STRING, 'true', 15],
                [T_STRING, 'json', 18],
                [T_CONSTANT_ENCAPSED_STRING, "'hi'", 18],
                [T_LNUMBER, 404, 18],
            ]
        );

        $start = $matches[0][0]['start'];
        $this->assertEquals($sampleFileTokens[$start][1], 'if');

        $end = $matches[0][0]['end'];
        $this->assertEquals($sampleFileTokens[$end], '}');

        $this->assertEquals($matches[0][1]['values'],
            [
                [T_VARIABLE, '$club', 23],
                [T_STRING, 'FALSE', 23],
                [T_STRING, 'json', 24],
                [T_CONSTANT_ENCAPSED_STRING, "'Hello'", 24],
                [T_LNUMBER, 403, 24],
            ]
        );

        $start = $matches[0][1]['start'];
        $this->assertEquals($sampleFileTokens[$start][1], 'if');

        $end = $matches[0][1]['end'];
        $this->assertEquals($sampleFileTokens[$end], '}');

        $start = $matches[1][0]['start'];
        $this->assertEquals($sampleFileTokens[$start][1], 'foo');

        $end = $matches[1][0]['end'];
        $this->assertEquals($sampleFileTokens[$end], ';');

        $start = $matches[1][1]['start'];
        $this->assertEquals($sampleFileTokens[$start][1], 'foo');

        $end = $matches[1][1]['end'];
        $this->assertEquals($sampleFileTokens[$end], ';');
    }

    /** @test */
    public function capturing_predicate()
    {
        $patterns = [
            "'<var>' = '<var>';" => [
                'replace' => '',
                'predicate' => function ($matches) {
                    return $matches['values'][0][1] === $matches['values'][1][1];
                }
            ],
        ];
        $startFile = '<?php
$var = 0;
$var = $var;
$user = $var;';
        $resultFile = '<?php
$var = 0;

$user = $var;';
        [$newVersion, $replacedAt] = PatternParser::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([3, 1], $replacedAt);
    }
}
