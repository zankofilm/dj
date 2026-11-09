<?php

namespace App\Services\Sms;

use InvalidArgumentException;

class PhoneNumber
{
    public static function normalize(string $value): string
    {
        $value = trim($value);
        $value = str_replace([' ', '-', '(', ')', "\u{200c}"], '', $value);

        // Convert Persian and Arabic-Indic digits to ASCII.
        $value = strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);

        if (preg_match('/^09[0-9]{9}$/', $value)) {
            $value = '+98'.substr($value, 1);
        } elseif (preg_match('/^989[0-9]{9}$/', $value)) {
            $value = '+'.$value;
        } elseif (preg_match('/^9[0-9]{9}$/', $value)) {
            $value = '+98'.$value;
        } elseif (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }

        if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $value)) {
            throw new InvalidArgumentException('شماره موبایل را با کد کشور وارد کنید؛ مانند ‎+989121234567');
        }

        return $value;
    }
}
