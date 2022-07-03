<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class FiltersPredicateTest extends BaseTestClass
{
    /** @test */
    public function inline_filters()
    {
        $startFile = '<?php h::h();g::h();k::h();';
        $resultFile = '<?php k::h();';

        $patterns = [
            'name' => [
                'search' => '<name>::h();',
                'replace' => "",
                'filters' => [
                    1 => [
                        [
                            function ($placeholderVal, $parameter) {
                                return in_array($placeholderVal[1], $parameter);
                            }, ['h', 'g'],
                        ],
                    ],
                ],
            ],
        ];

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1, 1], $replacedAt);
    }

    /** @test */
    public function inline_filters_1()
    {
        $startFile = '<?php h::h();g::h();k::h();';
        $resultFile = '<?php g::h();k::h();';

        $patterns = [
            'name' => [
                'search' => '<name>::h();',
                'replace' => "",
                'filters' => [
                    1 => [
                        [
                            function ($placeholderVal, $parameter) {
                                return in_array($placeholderVal[1], $parameter);
                            }, ['h', 'g'],
                        ],
                    ],
                ],
            ],
        ];

        [$newVersion, $replacedAt] = Searcher::searchReplaceFirst($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1], $replacedAt);

        ////////////////////////////////////////////

        $startFile = '<?php h::h();g::h();k::h();';
        $resultFile = '<?php h::h();k::h();';

        $patterns = [
            'name' => [
                'search' => '<name>::h();',
                'replace' => "",
                'filters' => [
                    1 => [
                        [
                            function ($placeholderVal, $parameter) {
                                return in_array($placeholderVal[1], $parameter);
                            }, ['k', 'g'],
                        ],
                    ],
                ],
            ],
        ];

        [$newVersion, $replacedAt] = Searcher::searchReplaceFirst($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function filters()
    {
        $startFile = '<?php h::h();g::h();k::h();';
        $resultFile = '<?php k::h();';

        ////////////////////////////////////////////

        $patterns = [
            'name' => [
                'search' => '"<name>"::h();',
                'replace' => "",
                'filters' => [
                    1 => [
                        'in_array' => ['h', 'g'],
                    ]
                ]
            ],
        ];
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1, 1], $replacedAt);

        ////////////////////////////////////////////

        $patterns = [
            'name' => [
                'search' => '"<name>"::h();',
                'replace' => "",
                'filters' => [
                    1 => [
                        'in_array' => 'h,g',
                    ]
                ]
            ],
        ];

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1, 1], $replacedAt);
    }

    /** @test */
    public function inline_params_for_name_filter()
    {
        $startFile = '<?php h::h();g::h();k::h();';
        $resultFile = '<?php k::h();';

        $patterns = [
            'name' => [
                'search' => '"<name:h,g>"::h();',
                'replace' => "",
            ],
        ];

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1, 1], $replacedAt);
    }

    /** @test */
    public function capturing_predicate()
    {
        $patterns = [
            "name" => [
                'search' => "'<var>' = '<var>';",
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
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([3], $replacedAt);
    }
}
