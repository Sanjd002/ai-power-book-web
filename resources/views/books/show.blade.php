@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <section class="relative mb-6 rounded-2xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 opacity-90"></div>
        <div class="relative z-10 p-8 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">{{ $book['title'] ?? 'Untitled' }}</h1>
                <p class="mt-2 text-indigo-100">
                    @php $authorNames = []; if(!empty($book['authors'])){ foreach($book['authors'] as $a){ $authorNames[] = $a['author']['name'] ?? 'Unknown'; }} @endphp
                    {{ count($authorNames) ? implode(', ', $authorNames) : 'Unknown Author' }}
                </p>
            </div>
            @if(!session('summary') && empty($existingSummary))
            <button id="open-summary-btn" class="mt-4 md:mt-0 bg-white text-indigo-700 hover:bg-indigo-50 px-4 py-2 rounded-xl shadow">
                Generate AI Summary
            </button>
            @endif
        </div>
    </section>
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">

    

            <div class="w-full max-w-xs mx-auto rounded-2xl overflow-hidden shadow">
                @php $coverId = $book['covers'][0] ?? ($book['cover_i'] ?? null); @endphp
                <div class="book-cover-wrapper aspect-[2/3] w-full">
                    @if($coverId)
                    <img src="https://covers.openlibrary.org/b/id/{{ $coverId }}-L.jpg" alt="{{ $book['title'] ?? 'Book cover' }}" class="w-full h-full object-cover">
                    @else
                    <img src="https://via.placeholder.com/300x450?text=No+Cover" alt="No cover available" class="w-full h-full object-cover">
                    @endif
                </div>
            </div>




            <!-- Book Info -->
            <div class="md:col-span-2 p-8 flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                    @foreach(($authorNames ?? []) as $name)
                        <span class="px-3 py-1 text-xs rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $name }}</span>
                    @endforeach
                </div>

                <p class="text-gray-700 mb-2">
                    <span class="font-semibold">Author:</span>
                    @if(!empty($book['authors']) && is_array($book['authors']))
                    @php $authorNames = []; foreach($book['authors'] as $a){ $authorNames[] = $a['author']['name'] ?? 'Unknown'; } @endphp
                    {{ implode(', ', $authorNames) }}
                    @elseif(!empty($book['author_name']) && is_array($book['author_name']))
                    {{ implode(', ', $book['author_name']) }}
                    @else
                    Unknown
                    @endif
                </p>

                <div class="bg-gray-50 border border-gray-200 p-6 rounded-xl shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                    <p class="text-gray-700 leading-relaxed">{{ $descriptionText ?? 'No description available.' }}</p>
                </div>

                <div id="inline-summary" class="mt-6 bg-white border border-gray-200 p-6 rounded-xl shadow-sm @if(!session('summary') && empty($existingSummary)) hidden @endif">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">AI Summary</h2>
                    <p id="inline-summary-text" class="text-gray-700 leading-relaxed whitespace-pre-line">
                        @if(session('summary'))
                            {{ session('summary') }}
                        @elseif(!empty($existingSummary))
                            {{ $existingSummary->summary }}
                        @endif
                    </p>
                </div

            </div>
        </div>
    </div>

    <!-- Summary Modal -->
    <div id="summary-modal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">AI Summary</h2>
                <button id="close-summary-btn" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div id="summary-content" class="text-gray-700 leading-relaxed whitespace-pre-line">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('summary-modal');
        const content = document.getElementById('summary-content');
        const openBtn = document.getElementById('open-summary-btn');
        const closeBtn = document.getElementById('close-summary-btn');
        const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

        function openModal() { modal.classList.add('active'); }
        function closeModal() { modal.classList.remove('active'); }

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        if (document.getElementById('open-summary-btn')) {
            openBtn.addEventListener('click', async () => {
                openModal();
                content.innerHTML = '<div class="min-h-[50vh] w-full flex items-center justify-center"><div class="flex flex-col items-center gap-4"><span class="summary-loading"></span><div class="text-gray-600">Generating summary...</div></div></div>';
                try {
                    const resp = await fetch('{{ route('books.summary', $olid) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    });
                    const data = await resp.json().catch(() => ({}));
                    if (!resp.ok) {
                        content.textContent = data.error || 'Summary generation failed.';
                        return;
                    }
                    const plain = (data.summary || '').replace(/\*\*/g, '').replace(/##/g, '');
                    content.textContent = plain || 'Summary generation failed.';
                    const inlineBox = document.getElementById('inline-summary');
                    const inlineText = document.getElementById('inline-summary-text');
                    if (inlineBox && inlineText && plain) {
                        inlineText.textContent = plain;
                        inlineBox.classList.remove('hidden');
                    }
                    openBtn.style.display = 'none';
                } catch (err) {
                    content.textContent = 'Network error. Please try again.';
                }
            });
        }
    });
</script>
@endsection