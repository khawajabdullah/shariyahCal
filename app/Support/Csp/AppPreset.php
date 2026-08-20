<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class AppPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::IMG, [Keyword::SELF, 'data:', 'blob:', 'https:'])
            ->add(Directive::FONT, ['fonts.gstatic.com'])
            ->add(Directive::STYLE, ['fonts.googleapis.com'])
            ->add(Directive::CONNECT, [Keyword::SELF]);

        if (app()->environment('local')) {
            $policy
                ->add(Directive::SCRIPT, [Keyword::UNSAFE_EVAL, 'http://127.0.0.1:5173', 'http://localhost:5173'])
                ->add(Directive::STYLE, ['http://127.0.0.1:5173', 'http://localhost:5173'])
                ->add(Directive::CONNECT, [
                    'ws://127.0.0.1:5173',
                    'ws://localhost:5173',
                    'http://127.0.0.1:5173',
                    'http://localhost:5173',
                ]);
        }
    }
}
