<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class ClassReferenceTest extends BaseTestClass
{
    /** @test */
    public function class_ref()
    {
        $patterns = [
            'name' => [
                'search' => "new '<class_ref>'(); new Foo(); new '<class_ref>'();",
                'replace' => '"<1>"::class;"<2>"::class;'],
        ];
        $startCode = '<?php new \App\Models\User(); new Foo(); new App2\Models2\User2();';
        $resultCode = '<?php \App\Models\User::class;App2\Models2\User2::class;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function class_ref32()
    {
        $patterns = [
            'name' => [
                'search' => "new '<class_ref>'(); new '<class_ref>'(); new '<class_ref>'();",
                'replace' => '"<1>"::class; "<2>"::class; "<3>"::class;'],
        ];
        $startCode = '<?php new User1(); new \App2\Models\User(); new App3\Models\User();';
        $resultCode = '<?php User1::class; \App2\Models\User::class; App3\Models\User::class;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function class_ref_3()
    {
        $patterns = [
            'name' => [
                'search' => "new '<class_ref>'();",
                'replace' => ''],
        ];
        $startCode = '<?php new User();';
        $resultCode = '<?php ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function class_ref_6()
    {
        $patterns = [
            'name' => [
                'search' => "'<class_ref>'();",
                'replace' => ''],
        ];
        $startCode = '<?php new User();';
        $resultCode = '<?php new ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function class_ref_4()
    {
        $patterns = [
            'name' => [
                'search' => "new '<class_ref>'();",
                'replace' => ''],
        ];
        $startCode = '<?php new \User();';
        $resultCode = '<?php ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);


        $startCode = '<?php new Models\User();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        $startCode = '<?php new \App\Models\User();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }
}
