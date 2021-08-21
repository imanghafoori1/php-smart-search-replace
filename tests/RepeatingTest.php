<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class RepeatingTest extends BaseTestClass
{
    /** @test */
    public function optional_comment_placeholder_5()
    {
        $patterns = [
            "name" => [
                'search' => "'<var>''<repeating:exp>';'<var>';",
                'replace' => '"<1>";"<2>";',
                'named_patterns' => [
                    'exp' => '->a("<str>", "<str>")',
                ],
            ],
        ];

        $startFile = '<?php $v->a("s1", "a1")->a("s2", "a2"); $r;';
        $resultFile = '<?php $v;$r;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function optional_comment_placeholder_q5()
    {
        $patterns = [
            "name"=> [
                'search' => "'<var>''<repeating:exp>';",
                'replace' => '"<1>""<repeating:1:subs>";',
                'named_patterns' => [
                    'exp' => '->a("<str>", "<str>")',
                    'subs' => '->a("<2>", "<1>")',
                ]
            ],
        ];

        $startFile = '<?php $v->a("s1", "a1")->a("s2", "a2")->a("g3", "k3");';
        $resultFile = '<?php $v->a("a1", "s1")->a("a2", "s2")->a("k3", "g3");';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function optional_comment_placeholder_qd5()
    {
        $patterns = [
            "name"=> [
                'search' => "User::where('<str>', '<str>')'<repeating:exp>'",
                'replace' => 'User::where(["<1>" => "<2>", "<repeating:1:subs>"])',
                'named_patterns' => [
                    'exp' => '->where("<str>", "<str>")',
                    'subs' => '"<1>" => "<2>", ',
                ]
            ],
        ];


        $startFile =  '<?php User::where("s1", "a1")->where("s2", "a2")->where("g3", "k3")->get();';
        $resultFile = '<?php User::where(["s1" => "a1", "s2" => "a2", "g3" => "k3", ])->get();';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function optional_comment_placeholder_qgd5()
    {
        $patterns = [
            "name"=> [
                'search' => "'<repeating:exp>'",
                'replace' => '->where(["<repeating:1:subs>"])',
                'named_patterns' => [
                    'exp' => '->where("<str>", "<str>")',
                    'subs' => '"<1>" => "<2>", ',
                ]
            ],
        ];


        $startFile =  '<?php $user->where("s1", "a1")->where("s2", "a2")->where("g3", "k3")->get();';
        $resultFile = '<?php $user->where(["s1" => "a1", "s2" => "a2", "g3" => "k3", ])->get();';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function optional_comment_placeholder_qg1()
    {
        $patterns = [
            "name"=> [
                'search' => "'<repeating:exp>'->get();",
                'replace' => '->where(["<repeating:1:subs>"])->met();',
                'named_patterns' => [
                    'exp' => '->where("<str>", "<str>")',
                    'subs' => '"<1>" => "<2>", ',
                ]
            ],
        ];


        $startFile =  '<?php $user->where("s1", "a1")->where("s2", "a2")->where("g3", "k3")->get();';
        $resultFile = '<?php $user->where(["s1" => "a1", "s2" => "a2", "g3" => "k3", ])->met();';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function optional_comment_placeholder_qrg1()
    {
        $patterns = [
            "name"=> [
                'search' => "'<repeating:exp>'->get();",
                'replace' => '->where(["<repeating:1:subs>"])->met();',
                'named_patterns' => [
                    'exp' => '->where("<str>", "<str>")',
                    'subs' => '"<1>" => "<2>", ',
                ]
            ],
        ];

        $startFile =  "<?php
        User::where('name', 'Iman')
            ->where('family', 'Ghafoori')
            ->where('job', 'be_to_che')
            ->where('website', 'codino.org')
            ->get();

        User::where('_name', 'Iman___')
            ->where('_family', 'Ghafoori___')
            ->where('_web_site', '_codino__org_')
            ->get();";

        $resultFile = "<?php
        User::where('name', 'Iman')
            ->where(['family' => 'Ghafoori', 'job' => 'be_to_che', 'website' => 'codino.org', ])->met();

        User::where('_name', 'Iman___')
            ->where(['_family' => 'Ghafoori___', '_web_site' => '_codino__org_', ])->met();";

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
