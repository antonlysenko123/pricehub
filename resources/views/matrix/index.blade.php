<!DOCTYPE html>
<html>
<head>
    <title>Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">Supplier Matrix</h1>

    {{-- Повідомлення --}}
    @if(session('status'))
        <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif
<form action="{{ route('matrix.import') }}" method="POST">
    @csrf
    <button class="px-4 py-2 bg-indigo-600 text-white rounded">
        Import Matrix
    </button>
</form>
    {{-- КНОПКА СИНХРОНІЗАЦІЇ --}}
    <form action="{{ route('matrix.sync') }}" method="POST" class="mb-4">
        @csrf
        <button class="px-4 py-2 rounded bg-emerald-600 text-white text-sm hover:bg-emerald-700">
            Update matrix
        </button>
    </form>

    {{-- ПРОГРЕС БАР --}}
    <div class="w-full bg-gray-200 rounded mb-6">
        <div class="js-progress-bar bg-blue-600 text-xs leading-none py-1 text-center text-white transition-all duration-500"
             style="width:0%">
            <span class="js-progress-label">0%</span>
        </div>
    </div>

    {{-- ФІЛЬТРИ --}}
    <form method="GET" class="bg-white p-4 rounded-xl shadow mb-6 grid grid-cols-6 gap-4">

        <input type="text" name="product_id" value="{{ request('product_id') }}"
               placeholder="Product ID" class="border rounded px-3 py-2">
<input type="text" name="product_name" value="{{ request('product_name') }}"
       placeholder="Product Name"
       class="border rounded px-3 py-2">
        <input type="text" name="supplier_name" value="{{ request('supplier_name') }}"
               placeholder="Supplier" class="border rounded px-3 py-2">

        <input type="text" name="supplier_sku" value="{{ request('supplier_sku') }}"
               placeholder="Supplier SKU" class="border rounded px-3 py-2">
               

        <input type="text" name="current_price" value="{{ request('current_price') }}"
               placeholder="Price" class="border rounded px-3 py-2">

        <input type="text" name="current_quantity" value="{{ request('current_quantity') }}"
               placeholder="Quantity" class="border rounded px-3 py-2">

        <div class="col-span-6 text-right">
            <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Search
            </button>
        </div>
    </form>

    {{-- ТАБЛИЦЯ --}}
    <div class="bg-white rounded-xl shadow overflow-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-200 sticky top-0">
                <tr>
                    <th class="p-3 text-left">Product ID</th>
                    <th class="p-3 text-left">Product Name</th>
                    <th class="p-3 text-left">Supplier</th>
                    <th class="p-3 text-left">Supplier SKU</th>
                    <th class="p-3 text-left">Price</th>
                    <th class="p-3 text-left">Quantity</th>
                    <th class="p-3 text-left">Synced</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="border-b hover:bg-gray-50 {{ $row->current_price ? '' : 'bg-red-50' }}">
                        <td class="p-3">{{ $row->product_id }}</td>
                        <td class="p-3 font-medium text-gray-800">
    {{ $row->product_name }}
</td>
                        <td class="p-3">{{ $row->supplier_name }}</td>
                        <td class="p-3">{{ $row->supplier_sku }}</td>
                        <td class="p-3 font-semibold {{ $row->current_price ? 'text-green-600' : 'text-red-600' }}">
                            {{ $row->current_price ?? '—' }}
                        </td>
                        <td class="p-3">{{ $row->current_quantity ?? '—' }}</td>
                        <td class="p-3 text-gray-500">{{ $row->last_synced_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
      {{ $rows->links('pagination::bootstrap-5') }} 
    </div>

</div>

{{-- ЄДИНИЙ SCRIPT --}}
<script>
function updateMatrixStatus() {
    fetch('/matrix/statuses')
        .then(res => res.json())
        .then(data => {

            if (!data) return;

            const bar = document.querySelector('.js-progress-bar');
            const label = document.querySelector('.js-progress-label');

            if (!bar || !label) return;

            const percent = data.progress || 0;

            bar.style.width = percent + '%';
            label.innerText = percent + '%';

            if (data.status === 'done') {
                bar.classList.remove('bg-blue-600');
                bar.classList.add('bg-green-600');
            } else {
                bar.classList.remove('bg-green-600');
                bar.classList.add('bg-blue-600');
            }
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', function() {
    updateMatrixStatus();
    setInterval(updateMatrixStatus, 1000);
});
</script>

</body>
</html>