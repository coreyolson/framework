<?php

namespace controllers;

class home
{
    public static function get_index()
    {
        view::echo('welcome', [
            'startup' => f::benchmark()->since('Startup'),
            'string'  => helpers\str::generate(8),
        ]);
    }
}
