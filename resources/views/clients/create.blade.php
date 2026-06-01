@extends('layouts.app')
@section('title', 'New Client')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('clients.store') }}">@csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Company Name</label><input name="company_name" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">PIC Name</label><input name="pic_name" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Phone</label><input name="phone" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Email</label><input name="email" type="email" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Industry</label><input name="industry" class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Status</label><select name="status" class="w-full border rounded px-3 py-2 text-sm"><option value="prospect">Prospect</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Address</label><textarea name="address" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded text-sm">Create Client</button>
            <a href="{{ route('clients.index') }}" class="text-gray-500 hover:underline text-sm py-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
