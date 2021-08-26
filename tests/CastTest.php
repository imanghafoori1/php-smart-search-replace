<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class Cast extends BaseTestClass
{
    /** @test */
    public function number_matching()
    {
        $patterns = [
            'name' => [
                'search' => '"<cast>"',
                'replace' => '',
            ]
        ];

        $startFile = '<?php (array) $a;';
        $resultFile = '<?php  $a;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php (bool) $a;';
        $resultFile = '<?php  $a;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php (int) $a;';
        $resultFile = '<?php  $a;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php (object) $a;';
        $resultFile = '<?php  $a;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
