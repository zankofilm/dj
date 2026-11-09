<?php

namespace App\Support;

final class Locale
{
    public const SUPPORTED = ['fa', 'ku', 'en'];

    public static function direction(string $locale): string
    {
        return in_array($locale, ['fa', 'ku'], true) ? 'rtl' : 'ltr';
    }
}
