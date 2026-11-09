<?php

namespace Tests\Feature;

use App\Support\Locale;
use PHPUnit\Framework\TestCase;

class LocaleDirectionTest extends TestCase
{
    public function test_farsi_and_sorani_are_rtl_and_english_is_ltr(): void
    {
        self::assertSame('rtl', Locale::direction('fa'));
        self::assertSame('rtl', Locale::direction('ku'));
        self::assertSame('ltr', Locale::direction('en'));
    }
}
