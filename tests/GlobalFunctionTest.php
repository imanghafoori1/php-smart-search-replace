<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\LaravelMicroscope\Tests\BaseTestClass;
use Imanghafoori\SearchReplace\Searcher;

class GlobalFunctionTest extends BaseTestClass
{
    /** @test */
    public function function_call1()
    {
        $patterns = [
            "name" => [
                'search' => "'<global_func_call:dd>'();",
                'replace' => ''
            ],
        ];

        $startFile_ = '<?php function dd(){} new dd();dd::  aa();$a->  dd(); jj();dd();dd(); \dd(); \kk();';
        $resultFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd(); jj();  \kk();';

        $tokens = token_get_all($startFile_);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function function_call()
    {
        $patterns = [
            "name" => [
                'search' => "'<global_func_call:dd,dump>'('<in_between>');",
                'replace' => ''
            ],
        ];


        $startFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd();dd(); dd(); dump();';
        $resultFile = '<?php function dd(){} new dd();dd::  aa();$a->  dd();  ';

        $tokens = token_get_all($startFile);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);
    }

}
