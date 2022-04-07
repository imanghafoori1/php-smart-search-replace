<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class StatementTest extends BaseTestClass
{
    /** @test */
    public function statement()
    {
        $patterns = [
            'name' => [
                'search' => '$user = "<statement>"' ,
                'replace' => '"<1>"'
            ],
        ];

        $startCode = '<?php $user = function () { $a = 1; $b = "end"; };';
        $resultCode = '<?php function () { $a = 1; $b = "end"; };';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function statement_2()
    {
        $patterns = [
            'name' => [
                'search' => '"<statement>";$a = 1;' ,
                'replace' => '<1>'
            ],
        ];
        $startCode = '<?php $user = where(function () { $a = 1; $a; }); $a = 1;';
        $resultCode = '<?php $user = where(function () { $a = 1; $a; })';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function statement_3()
    {
        $patterns = [
            'name' => [
                'search' => '["a" => "a", "b" => "<statement>" ];' ,
                'replace' => '"<1>"'
            ],
        ];
        $startCode = '<?php ["a" => "a", "b" => User::where(function() { "a"; })->get() ];';
        $resultCode = '<?php User::where(function() { "a"; })->get() ';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function statement_4()
    {
        $patterns = [
            'name' => [
                'search' => '"<statement>";' ,
                'replace' => ''
            ],
        ];

        $startCode = '<?php $user = where(function () { $a = 1; $a; });';
        $resultCode = '<?php ';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
    }

    /** @test */
    public function statement_5()
    {
        $patterns = [
            'name' => [
                'search' => '$user = "<statement>";"<statement>";' ,
                'replace' => '"<1>""<2>"'
            ],
        ];
        $startCode = '<?php $user = where(function () { $a = 1; $a; }); $a = 1;';
        $resultCode = '<?php where(function () { $a = 1; $a; })$a = 1';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function statement_6()
    {
        $patterns = [
            'name' => [
                'search' => '$user = <statement>;<statement>;' ,
                'replace' => '<1><2>'
            ],
        ];
        $startCode = '<?php $user = where(function () { $a = 1; $a; }); $a = 1;';
        $resultCode = '<?php where(function () { $a = 1; $a; })$a = 1';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

}
