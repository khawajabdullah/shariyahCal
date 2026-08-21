<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic;

class AppPolicy extends Basic
{
    public function configure()
    {
        parent::configure();

        $this
            ->addDirective(Directive::IMG, ['data:', 'blob:', 'https:'])
            ->addDirective(Directive::FONT, ['fonts.gstatic.com'])
            ->addDirective(Directive::STYLE, ['fonts.googleapis.com'])
            ->addDirective(Directive::CONNECT, [Keyword::SELF]);

        if (app()->environment('local')) {
            $this
                ->addDirective(Directive::SCRIPT, [Keyword::UNSAFE_EVAL, 'http://127.0.0.1:5173', 'http://localhost:5173'])
                ->addDirective(Directive::STYLE, ['http://127.0.0.1:5173', 'http://localhost:5173'])
                ->addDirective(Directive::CONNECT, [
                    'ws://127.0.0.1:5173',
                    'ws://localhost:5173',
                    'http://127.0.0.1:5173',
                    'http://localhost:5173',
                ]);
        }
    }
}
