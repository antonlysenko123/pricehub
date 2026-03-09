<!DOCTYPE html>
<html>
<head>
    <title>Price Stack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">Product Price Stack</h1>

    <form method="GET" class="bg-white p-4 rounded-xl shadow mb-6 grid grid-cols-6 gap-4"

        <input type="text" name="product_id" value="{{ request('product_id') }}"
               placeholder="Product ID"
               class="border rounded px-3 py-2">
               
            <input type="text" name="product_name" value="{{ request('product_name') }}"
       placeholder="Product Name"
       class="border rounded px-3 py-2">   

        <input type="text" name="supplier_name" value="{{ request('supplier_name') }}"
               placeholder="Supplier"
               class="border rounded px-3 py-2">

        <input type="text" name="supplier_sku" value="{{ request('supplier_sku') }}"
               placeholder="Supplier SKU"
               class="border rounded px-3 py-2">

        <input type="text" name="price" value="{{ request('price') }}"
               placeholder="Price"
               class="border rounded px-3 py-2">

        <input type="text" name="quantity" value="{{ request('quantity') }}"
               placeholder="Quantity"
               class="border rounded px-3 py-2">

        <div class="col-span-5 flex justify-end">
            <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Search
            </button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow overflow-auto">

        <table class="min-w-full text-sm">
            <thead class="bg-gray-200 sticky top-0">
                <tr>
                    <th class="p-3 text-left">Product ID</th>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Supplier</th>
                    <th class="p-3 text-left">Supplier SKU</th>
                    <th class="p-3 text-left">Price</th>
                    <th class="p-3 text-left">Quantity</th>
                    <th class="p-3 text-left">Availability</th>
                    <th class="p-3 text-left">Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prices as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $row->product_id }}</td>
                        <td class="p-3">{{ $row->product_name }}</td>
                        <td class="p-3">{{ $row->supplier_name }}</td>
                        <td class="p-3">{{ $row->supplier_sku }}</td>
                        <td class="p-3 font-semibold text-green-600">{{ $row->price }}</td>
                        <td class="p-3">{{ $row->quantity }}</td>
                        <td class="p-3">{{ $row->availability_status }}</td>
                        <td class="p-3 text-gray-500">{{ $row->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="mt-6">
        {{ $prices->links('pagination::bootstrap-5') }}
    </div>

</div>

</body>
</html>