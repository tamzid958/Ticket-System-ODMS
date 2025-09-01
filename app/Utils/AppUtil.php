<?php

namespace App\Utils;

class AppUtil
{

    public static function generateColorCode(string $name): string
    {
        $hash = md5($name);
        $color = '';

        for ($i = 0; $i < 3; $i++) {
            $byte = hexdec(substr($hash, $i * 2, 2));
            // restrict to dark range (0–128)
            $darkByte = $byte % 128;
            $color .= str_pad(dechex($darkByte), 2, '0', STR_PAD_LEFT);
        }

        return "#$color";
    }
}
