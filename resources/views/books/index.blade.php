@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        @foreach($books as $book)
        <a href="{{ route('books.show', $book['key'] ? basename($book['key']) : '') }}"
           class="block bg-white rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transition transform overflow-hidden group relative">
            
            <!-- Book Cover -->
            <div class="aspect-[2/3] bg-gray-100 relative">
                @if(!empty($book['cover_i']))
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-100 loader-wrapper">
                        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-300 border-t-blue-500 h-8 w-8 animate-spin"></div>
                    </div>
                    <img 
                        src="https://covers.openlibrary.org/b/id/{{ $book['cover_i'] }}-M.jpg"
                        alt="{{ $book['title'] ?? 'Book cover' }}"
                        class="w-full h-full object-cover opacity-0 transition-opacity duration-700 book-image"
                        loading="lazy">
                @else
                    <img src="https://via.placeholder.com/200x300?text=No+Cover"
                         alt="No cover available"
                         class="w-full h-full object-cover">
                @endif
            </div>

            <!-- Book Info -->
            <div class="p-3">
                <h2 class="text-md font-semibold text-gray-800 truncate group-hover:text-blue-600">
                    {{ $book['title'] ?? 'Untitled' }}
                </h2>
                <p class="text-xs text-gray-600 mt-1">
                    <span class="font-medium">Author:</span>
                    @if(!empty($book['author_name']) && is_array($book['author_name']))
                        {{ implode(', ', $book['author_name']) }}
                    @else
                        Unknown
                    @endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

<style>
    /* Smooth loader animation */
    .loader {
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    // Wait until all images are loaded before removing their spinners
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.book-image');
        images.forEach(img => {
            img.addEventListener('load', () => {
                const wrapper = img.closest('.relative').querySelector('.loader-wrapper');
                if (wrapper) {
                    wrapper.style.opacity = '0';
                    wrapper.style.transition = 'opacity 0.4s ease';
                    setTimeout(() => wrapper.remove(), 400);
                }
                img.style.opacity = '1';
            });

            // If already cached by browser
            if (img.complete) {
                img.dispatchEvent(new Event('load'));
            }
        });
    });
</script>
@endsection
