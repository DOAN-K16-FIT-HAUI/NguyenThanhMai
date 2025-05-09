@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-8">
        <h2 class="text-2xl font-bold text-green-700 mb-6">Thêm mới Danh mục</h2>

        <form action="{{ route('admin.categories.store') }}" method="POST" class="bg-white shadow-md rounded p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Category Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" value="{{ old('name') }}" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Mô tả</label>
                <textarea name="description" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Lưu</button>
            </div>
        </form>
    </div>
@endsection
