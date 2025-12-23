@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow">
        <div class="p-4">
            <form action="{{ route('summaries.clear') }}" method="POST" class="flex justify-end mb-4" onsubmit="return confirm('Delete all history?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow">Delete All History</button>
            </form>
            <div class="grid grid-cols-4 gap-6 w-full">
                @forelse($summaries as $item)
                <div class="bg-white border border-gray-200 rounded-xl shadow hover:shadow-lg transition overflow-hidden">
                    <a href="{{ route('books.show', $item->olid) }}" class="block">
                        <div class="aspect-[2/3] bg-gray-100">
                            @if($item->cover_id)
                            <img src="https://covers.openlibrary.org/b/id/{{ $item->cover_id }}-M.jpg" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            @else
                            <img src="https://via.placeholder.com/300x450?text=No+Cover" alt="No cover available" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 truncate">{{ $item->title }}</h3>
                            <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($item->summary, 180) }}</p>
                            <p class="text-xs text-gray-500 mt-3">{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-span-1 md:col-span-2">
                    <p class="text-gray-600">No summaries yet.</p>
                </div>
                @endforelse
            </div>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $summaries->links() }}
        </div>
    </div>
</div>
@endsection