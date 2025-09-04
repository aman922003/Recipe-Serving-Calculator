<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecipeServingController extends Controller
{
    // ✅ Base unit: teaspoon (tsp) for VOLUME
    private $unitToTsp = [
        'tsp'     => 1,
        'tbsp'    => 3,          // 1 tbsp = 3 tsp
        'floz'    => 6,          // 1 fl oz = 2 tbsp = 6 tsp
        'cup'     => 48,         // 1 cup = 48 tsp
        'pint'    => 96,         // 1 pint = 96 tsp
        'quart'   => 192,        // 1 quart = 192 tsp
        'gallon'  => 768,        // 1 gallon = 768 tsp
        'ml'      => 0.202884,   // 1 ml = 0.202884 tsp
        'liter'   => 202.884,    // 1 liter = 202.884 tsp
    ];

    // ✅ Base unit: ounce (oz) for WEIGHT
    private $unitToOz = [
        'oz'  => 1,
        'lb'  => 16,         // 1 lb = 16 oz
        'g'   => 0.035274,   // 1 g = 0.035274 oz
        'kg'  => 35.274,     // 1 kg = 35.274 oz
    ];

    private $unitLabels = [
        // Volume
        'tsp'     => 'Teaspoon (t)',
        'tbsp'    => 'Tablespoon (T)',
        'floz'    => 'Fluid Ounce (fl oz)',
        'cup'     => 'Cup (c)',
        'pint'    => 'Pint (pt)',
        'quart'   => 'Quart (qt)',
        'gallon'  => 'Gallon (gal)',
        'ml'      => 'Milliliter (ml)',
        'liter'   => 'Liter (L)',

        // Weight
        'oz'      => 'Ounce (oz)',
        'lb'      => 'Pound (lb)',
        'g'       => 'Gram (g)',
        'kg'      => 'Kilogram (kg)',
    ];

    public function index()
    {
        return view('recipe-serving.index', [
            'units' => $this->unitLabels,
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'quantity' => 'required|string', 
            'unit'     => 'required|in:' . implode(',', array_keys($this->unitLabels)),
            'servings' => 'required|integer|min:1',
        ]);

        // ✅ Parse quantity like "2 1/4", "3/4", "2.25"
        $quantity = $this->parseMixedNumber($request->input('quantity'));
        if ($quantity === null) {
            return response()->json(['error' => 'Invalid number format'], 422);
        }

        $unit     = $request->input('unit');
        $servings = (int) $request->input('servings');

        // ✅ Decide if it's volume or weight
        if (isset($this->unitToTsp[$unit])) {
            // VOLUME
            $totalTsp = $quantity * $this->unitToTsp[$unit] * $servings;
            $scaled   = $this->convertTspToUnits($totalTsp);
        } elseif (isset($this->unitToOz[$unit])) {
            // WEIGHT
            $totalOz = $quantity * $this->unitToOz[$unit] * $servings;
            $scaled  = $this->convertOzToUnits($totalOz);
        } else {
            return response()->json(['error' => 'Unknown unit'], 422);
        }

        return response()->json([
            'scaled' => $scaled,
        ]);
    }

    /**
     * ✅ Parse input like "2 1/4", "3/4", "2.25"
     */
    private function parseMixedNumber(string $input): ?float
    {
        $input = trim($input);

        // Case: "whole fraction" → e.g. "2 1/4"
        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $input, $m)) {
            $whole = (int) $m[1];
            $num   = (int) $m[2];
            $den   = (int) $m[3];
            return $den > 0 ? $whole + ($num / $den) : null;
        }

        // Case: "fraction only" → e.g. "3/4"
        if (preg_match('/^(\d+)\/(\d+)$/', $input, $m)) {
            $num = (int) $m[1];
            $den = (int) $m[2];
            return $den > 0 ? $num / $den : null;
        }

        // Case: decimal or whole number
        if (is_numeric($input)) {
            return (float) $input;
        }

        return null; // ❌ invalid input
    }

    /**
     * ✅ Convert tsp into kitchen units (c, T, t, etc.)
     */
    private function convertTspToUnits(float $tsp): string
    {
        $eps = 1e-9;
        $parts = [];

        // Gallons
        if ($tsp >= 768) {
            $gal = floor(($tsp + $eps) / 768);
            $parts[] = $gal . ' gal';
            $tsp -= $gal * 768;
        }

        // Quarts
        if ($tsp >= 192) {
            $qt = floor(($tsp + $eps) / 192);
            $parts[] = $qt . ' qt';
            $tsp -= $qt * 192;
        }

        // Pints
        if ($tsp >= 96) {
            $pt = floor(($tsp + $eps) / 96);
            $parts[] = $pt . ' pt';
            $tsp -= $pt * 96;
        }

        // Cups
        if ($tsp >= 48) {
            $cups = floor(($tsp + $eps) / 48);
            $parts[] = $cups . ' c.';
            $tsp -= $cups * 48;
        }

        // Fluid ounces
        if ($tsp >= 6) {
            $floz = floor(($tsp + $eps) / 6);
            $parts[] = $floz . ' fl oz';
            $tsp -= $floz * 6;
        }

        // Tablespoons
        if ($tsp >= 3) {
            $tbsp = floor(($tsp + $eps) / 3);
            $parts[] = $tbsp . ' T.';
            $tsp -= $tbsp * 3;
        }

        // Teaspoons (whole + fraction)
        $whole = (int) floor($tsp + $eps);
        $frac  = $tsp - $whole;

        if ($whole > 0 && $frac > $eps) {
            $parts[] = $whole . ' ' . $this->fractionOnly($frac) . ' t.';
        } elseif ($whole > 0) {
            $parts[] = $whole . ' t.';
        } elseif ($frac > $eps) {
            $parts[] = $this->fractionOnly($frac) . ' t.';
        }

        return !empty($parts) ? implode(' + ', $parts) : '0';
    }

    /**
     * ✅ Convert oz into lb + oz
     */
    private function convertOzToUnits(float $oz): string
    {
        $eps = 1e-9;
        $parts = [];

        // Pounds
        if ($oz >= 16) {
            $lb = floor(($oz + $eps) / 16);
            $parts[] = $lb . ' lb';
            $oz -= $lb * 16;
        }

        // Ounces (whole + fraction)
        $whole = (int) floor($oz + $eps);
        $frac  = $oz - $whole;

        if ($whole > 0 && $frac > $eps) {
            $parts[] = $whole . ' ' . $this->fractionOnly($frac) . ' oz';
        } elseif ($whole > 0) {
            $parts[] = $whole . ' oz';
        } elseif ($frac > $eps) {
            $parts[] = $this->fractionOnly($frac) . ' oz';
        }

        return !empty($parts) ? implode(' + ', $parts) : '0';
    }

    /**
     * ✅ Convert decimal to nice fraction (1/2, 1/3, 1/4, etc.)
     */
    private function fractionOnly(float $x): string
    {
        $x = max(0.0, min(0.999999, $x));
        if ($x < 1e-9) return '0';

        $denoms = [2, 3, 4, 6, 8, 12, 16];
        $bestN = 0;
        $bestD = 1;
        $bestErr = PHP_FLOAT_MAX;

        foreach ($denoms as $d) {
            $n = (int) round($x * $d);
            $err = abs($x - ($n / $d));
            if ($err < $bestErr - 1e-12) {
                $bestErr = $err;
                $bestN = $n;
                $bestD = $d;
            }
        }

        if ($bestN == 0) return '0';
        $this->reduce($bestN, $bestD);

        return "{$bestN}/{$bestD}";
    }

    private function reduce(int &$n, int &$d): void
    {
        $g = $this->gcd($n, $d);
        if ($g > 1) {
            $n /= $g;
            $d /= $g;
        }
    }

    private function gcd(int $a, int $b): int
    {
        $a = abs($a); $b = abs($b);
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return $a;
    }
}