<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\LaravelMicroscope\Tests\BaseTestClass;
use Imanghafoori\SearchReplace\Searcher;

class ExpressionTest extends BaseTestClass
{
    /** @test */
    public function expression()
    {
        $patterns = [
            '$user = "<expression>"' => ['replace' => '"<1>"'],
        ];
        ////////////////////////////////////

        $startCode = '<?php $user = function () { $a = 1; $b = "end"; };';
        $resultCode = '<?php function () { $a = 1; $b = "end"; };';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        ////////////////////////////////////

        $startCode = '<?php $user = where(function () { $a = 1; $a; });';
        $resultCode = '<?php where(function () { $a = 1; $a; });';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

}
