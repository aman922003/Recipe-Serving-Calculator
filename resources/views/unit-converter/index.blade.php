@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6 text-center text-orange-600">U.S. Kitchen Unit Converter</h1>

    <form action="{{ route('unit-converter.convert') }}" method="POST" class="mb-6 bg-white p-6 rounded shadow-md max-w-xl mx-auto">
        @csrf
        <div class="flex flex-col gap-2 items-center">
            <div class="flex gap-4 items-center justify-center">
                <input type="text" name="value" value="{{ old('value', $inputValue ?? '') }}" 
                       class="border px-3 py-2 rounded w-32" placeholder="Enter fraction or decimal">

                <select name="unit" class="border px-3 py-2 rounded">
                    @foreach($units as $key => $label)
                        <option value="{{ $key }}" {{ (old('unit', $inputUnit ?? '') == $key) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">Convert</button>
            </div>
            <p class="text-sm text-gray-500 mt-1">You can enter a decimal (e.g., 0.75) or fraction (e.g., 3/4)</p>
        </div>
        @error('value')
            <p class="text-red-500 mt-2">{{ $message }}</p>
        @enderror
    </form>

    @if(isset($results))
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-orange-100">
                <tr>
                    <th class="border px-3 py-2 text-left">Unit</th>
                    <th class="border px-3 py-2 text-left">Value (Fraction)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $unit => $value)
                <tr class="hover:bg-yellow-50">
                    <td class="border px-3 py-2">{{ $units[$unit] }}</td>
                    <td class="border px-3 py-2 font-mono">{{ $value }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
