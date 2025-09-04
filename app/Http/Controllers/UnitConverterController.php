<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UnitConverterController extends Controller
{
    // Base table: unit => equivalent in pinches
    private $unitToPinch = [
        'pinch' => 1,
        'tsp' => 16,
        'tbsp' => 48,
        'fl_oz' => 96,
        'cup' => 768,
        'pint' => 1536,
        'quart' => 3072,
        'gallon' => 12288,
        'oz' => 96,
        'lb' => 1536,
    ];

    private $unitLabels = [
        'pinch' => 'Pinch',
        'tsp' => 'Tsp',
        'tbsp' => 'Tbsp',
        'fl_oz' => 'Fl oz',
        'cup' => 'Cup',
        'pint' => 'Pint',
        'quart' => 'Quart',
        'gallon' => 'Gallon',
        'oz' => 'Ounce (oz)',
        'lb' => 'Pound (lb)',
    ];

    // Show the input form
    public function index()
    {
        return view('unit-converter.index', ['units' => $this->unitLabels]);
    }

    // Handle conversion
    public function convert(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'unit' => 'required|in:' . implode(',', array_keys($this->unitToPinch)),
        ]);

        $value = $request->input('value');
        $unit = $request->input('unit');

        // Convert fraction to decimal if necessary
        if (strpos($value, '/') !== false) {
            $parts = explode('/', $value);
            if (count($parts) == 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $value = floatval($parts[0]) / floatval($parts[1]);
            } else {
                return back()->withErrors(['value' => 'Invalid fraction format. Use a/b'])->withInput();
            }
        } else {
            if (!is_numeric($value)) {
                return back()->withErrors(['value' => 'Invalid number'])->withInput();
            }
            $value = floatval($value);
        }

        $sourceInPinches = $value * $this->unitToPinch[$unit];

        $results = [];
        foreach ($this->unitToPinch as $targetUnit => $pinchValue) {
            $results[$targetUnit] = $this->toFraction($sourceInPinches / $pinchValue);
        }

        return view('unit-converter.index', [
            'units' => $this->unitLabels,
            'results' => $results,
            'inputValue' => $request->input('value'),
            'inputUnit' => $unit,
        ]);
    }

    // Convert decimal to fraction
    private function toFraction($decimal)
    {
        if ($decimal == 0) return '0';

        $denominator = 256; // maximum denominator
        $numerator = round($decimal * $denominator);

        $gcd = $this->gcd($numerator, $denominator);

        $numerator /= $gcd;
        $denominator /= $gcd;

        // If whole number
        if ($denominator == 1) return (string)$numerator;

        return "{$numerator}/{$denominator}";
    }

    // Greatest Common Divisor
    private function gcd($a, $b)
    {
        return $b == 0 ? $a : $this->gcd($b, $a % $b);
    }
}
