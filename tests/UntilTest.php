<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\LaravelMicroscope\Tests\BaseTestClass;
use Imanghafoori\SearchReplace\Searcher;

class UntilTest extends BaseTestClass
{
    /** @test */
    public function until_placeholder()
    {
        $patterns = [
            'return response()"<until>";' => ['replace' => 'response()"<1>"->throwResponse();'],
        ];

        $startFile = file_get_contents(__DIR__.'/stubs/SimplePostController.stub');
        $resultFile = file_get_contents(__DIR__.'/stubs/SimplePostController2.stub');
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([16, 23], $replacedAt);
    }

    /** @test */
    public function in_between()
    {
        $patterns = [
            "if('<in_between>'){}" => ['replace' => 'if(true) {"<1>";}'],
        ];

        $startFile = '<?php if(foo()->bar()) {}';
        $resultFile = '<?php if(true) {foo()->bar();}';

        $tokens = token_get_all($startFile);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function function_call()
    {

        $patterns = [
            "'<global_func_call:dd,dump>'('<in_between>');" => [
                'replace' => ''
            ],
        ];


        $startFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd();dd(); dd(); dump();';
        $resultFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd();  ';

        $tokens = token_get_all($startFile);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);

        $patterns = [
            ";'<global_func_call:dd>'();" => ['replace' => ''],
        ];

        $startFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd();dd(); dd();';
        $resultFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd()';

        $tokens = token_get_all($startFile);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);
    }

}
