<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class VisibilityTest extends BaseTestClass
{
    /** @test */
    public function number_matching()
    {
        $patterns = [
            'name' => [
                'search' => '"<visibility>"',
                'replace' => '',
            ]
        ];

        $startFile = '<?php class A {public function foo (){}}';
        $resultFile = '<?php class A { function foo (){}}';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);


        $startFile = '<?php class A {private function foo(){}}';
        $resultFile = '<?php class A { function foo(){}}';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php class A {protected function foo(){}}';
        $resultFile = '<?php class A { function foo(){}}';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
