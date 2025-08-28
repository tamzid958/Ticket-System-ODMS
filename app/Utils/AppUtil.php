<?php

namespace App\Utils;

class AppUtil
{

    public static function generateColorCode(string $name): string
    {
        $hash = md5($name);
        $lastFour = '';

        for ($i = 0; $i < 2; $i++) {
            $byte = hexdec(substr($hash, $i * 2, 2));
            $lightByte = 128 + ($byte % 128);
            $lastFour .= str_pad(dechex($lightByte), 2, '0', STR_PAD_LEFT);
        }

        return '#FF' . $lastFour;
    }
}
