<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\PatternParser;
use Imanghafoori\SearchReplace\Searcher;
use Imanghafoori\SearchReplace\TokenCompare;
use PHPUnit\Framework\TestCase;

class PostReplaceTest extends TestCase
{
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
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
    }
}
