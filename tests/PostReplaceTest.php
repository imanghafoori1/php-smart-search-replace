<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class PostReplaceTest extends BaseTestClass
{
    /** @test */
    public function post_replace()
    {
        $patterns = [
            "name" => [
                'search' => ']',
                'replace' => ',]',
                'post_replace' => [
                    'name1' => ['search' => ',,]', 'replace' => ',]'],
                    'name2' => ['search' => '[,]', 'replace' => '[]'],
                ],
            ]
        ];

        $startFile = '<?php [1,2,3]; [1,2,3,]; [ ]; [/**/ ];';

        $resultFile = '<?php [1,2,3,]; [1,2,3,]; []; [];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
    }
}
