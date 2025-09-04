@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6 text-center text-green-600">Recipe Serving Calculator</h1>

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
        <div class="flex flex-col gap-4">
            <div class="flex gap-2 items-center">
                <input type="text" id="quantity" placeholder="Quantity (e.g., 3/4)" class="border px-3 py-2 rounded w-24">
                <select id="unit" class="border px-3 py-2 rounded">
                    @foreach($units as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>sadsdsdsd

            <div class="flex items-center gap-2">
                <button id="decrease" class="bg-gray-300 hover:bg-gray-400 px-3 py-1 rounded">-</button>
                <span>Servings: <span id="servings">1</span></span>
                <button id="increase" class="bg-gray-300 hover:bg-gray-400 px-3 py-1 rounded">+</button>
            </div>

            <div class="mt-4">
                <h2 class="text-lg font-semibold">Scaled Measurement:</h2>
                <p id="scaledResult" class="text-xl font-mono mt-2 text-blue-600">0</p>
            </div>
        </div>
    </div>
</div>

<script>
    let servings = 1;

    function updateResult() {
        const quantity = document.getElementById('quantity').value;
        const unit = document.getElementById('unit').value;

        fetch("{{ route('recipe-serving.calculate') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ quantity, unit, servings })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('scaledResult').innerText = data.scaled;
        });
    }

    document.getElementById('increase').addEventListener('click', () => {
        servings++;
        document.getElementById('servings').innerText = servings;
        updateResult();
    });

    document.getElementById('decrease').addEventListener('click', () => {
        if(servings > 1) {
            servings--;
            document.getElementById('servings').innerText = servings;
            updateResult();
        }
    });

    document.getElementById('quantity').addEventListener('input', updateResult);
    document.getElementById('unit').addEventListener('change', updateResult);
</script>
@endsection
