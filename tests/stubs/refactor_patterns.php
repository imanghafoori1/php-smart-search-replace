<?php

return [
    "name" => [
        'search' => "if (!'<variable>' && '<boolean>') { return response()->'<name>'(['message' => __('<string>'),], '<number>'); }",
        'replace' => 'Foo::bar("<1>", "<2>", "<3>"(), "<4>");'
    ],

    'name2' => [
        'search' => 'foo(false, true, null);',
        'replace' => 'bar("hi");'
    ],
];
