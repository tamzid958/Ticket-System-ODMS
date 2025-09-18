<?php

namespace App\Utils;

class AppUtil
{

    public static function generateColorCode(string $name): string
    {
        $hash = md5($name);

        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        // Scale into a mid–bright range (say 80–200) for better visual separation
        $r = 80 + ($r % 120);
        $g = 80 + ($g % 120);
        $b = 80 + ($b % 120);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    public static function getTreeMapConfig($clientNames, $amounts, $colors): array
    {
        return [
            'chart' => [
                'type' => 'treemap',
                'height' => 400,
            ],
            'series' => [
                [
                    'data' => collect($clientNames)->map(function ($name, $i) use ($amounts, $colors) {
                        return [
                            'x' => $name,
                            'y' => $amounts[$i],
                        ];
                    })->toArray(),
                ],
            ],
            'colors' => $colors,
        ];
    }

    public static function getDonutChartConfig($clientNames, $amounts, $colors): array
    {
        return [
            'chart' => [
                'type' => 'donut',
                'height' => 400,
            ],
            'series' => $amounts,
            'labels' => $clientNames,
            'colors' => $colors,
            'legend' => [
                'position' => 'right',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                    ],
                ],
            ],
        ];
    }

    public static function getHistogramChartConfig($categories, $values, $colors): array
    {
        return [
            'chart' => [
                'type' => 'bar',   // or 'column' if you prefer vertical bars
                'height' => 400,
            ],
            'series' => [
                [
                    'name' => 'Values',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
            ],
            'colors' => $colors,
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => false, // set true if you want horizontal histogram
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

    public static function getLineChartConfig($categories, $values, $colors): array
    {
        return [
            'chart' => [
                'type' => 'line',
                'height' => 400,
            ],
            'series' => [
                [
                    'name' => 'Values',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
            ],
            'colors' => $colors,
            'stroke' => [
                'curve' => 'smooth', // 'straight' or 'smooth'
                'width' => 2,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

    public static function getAreaChartConfig($categories, $values, $colors): array
    {
        return [
            'chart' => [
                'type' => 'area',
                'height' => 400,
            ],
            'series' => [
                [
                    'name' => 'Values',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
            ],
            'colors' => $colors,
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.4,
                    'opacityTo' => 0.1,
                    'stops' => [0, 90, 100],
                ],
            ],
        ];
    }

    public static function getRadarChartConfig($categories, $values, $colors): array
    {
        return [
            'chart' => [
                'type' => 'radar',
                'height' => 400,
            ],
            'series' => [
                [
                    'name' => 'Values',
                    'data' => $values,
                ],
            ],
            'labels' => $categories,
            'colors' => $colors,
            'stroke' => [
                'width' => 2,
            ],
            'fill' => [
                'opacity' => 0.3,
            ],
            'markers' => [
                'size' => 4,
            ],
        ];
    }

    public static function getPolarAreaChartConfig($categories, $values, $colors): array
    {
        return [
            'chart' => [
                'type' => 'polarArea',
                'height' => 400,
            ],
            'series' => $values,
            'labels' => $categories,
            'colors' => $colors,
            'stroke' => [
                'colors' => ['#fff'],
            ],
            'fill' => [
                'opacity' => 0.8,
            ],
            'legend' => [
                'position' => 'right',
            ],
        ];
    }
}
