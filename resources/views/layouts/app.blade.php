<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Powered Book Summary</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Loading Progress Bar */
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            width: 0%;
            transition: width 0.3s ease;
            z-index: 9999;
        }

        /* Image Loading State */
        .book-cover-wrapper {
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .book-cover-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spinCentered 1s linear infinite;
        }

        .book-cover-wrapper img {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .book-cover-wrapper img.loaded {
            opacity: 1;
        }

        .book-cover-wrapper.loaded::before {
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinCentered {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            height: 70vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            position: relative;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Dropdown Styles */
        [x-cloak] { display: none !important; }
        .dropdown-content {
            position: absolute;
            background-color: white;
            min-width: 220px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 100;
            top: 100%;
            left: 0;
            margin-top: 0;
        }

        /* Summary Loading Animation */
        .summary-loading {
            display: inline-block;
            width: 56px;
            height: 56px;
            border: 5px solid rgba(0, 0, 0, 0.12);
            border-radius: 50%;
            border-top-color: #4f46e5;
            animation: spin 0.9s linear infinite;
            will-change: transform;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Progress Bar -->
    <div id="progress-bar" class="progress-bar"></div>

    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent hover:from-indigo-700 hover:to-purple-700 transition-all">
                            Book Summary
                        </a>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-6">
                        <div class="dropdown relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-gray-700 hover:text-indigo-600 font-medium transition flex items-center" @click="open = !open">
                                Fiction
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="dropdown-content" x-cloak x-show="open" x-transition.opacity @click.outside="open = false">
                                <a href="{{ route('books.search') }}?q=subject:fiction" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">All Fiction</a>
                                <a href="{{ route('books.search') }}?q=subject:fantasy" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Fantasy</a>
                                <a href="{{ route('books.search') }}?q=subject:science_fiction" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Science Fiction</a>
                                <a href="{{ route('books.search') }}?q=subject:horror" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Horror</a>
                                <a href="{{ route('books.search') }}?q=subject:historical_fiction" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Historical Fiction</a>
                                <a href="{{ route('books.search') }}?q=subject:classics" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Classics</a>
                                <a href="{{ route('books.search') }}?q=subject:adventure" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Adventure</a>
                                <a href="{{ route('books.search') }}?q=subject:young_adult" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Young Adult</a>
                                <a href="{{ route('books.search') }}?q=subject:children" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Children</a>
                                <a href="{{ route('books.search') }}?q=subject:short_stories" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Short Stories</a>
                                <a href="{{ route('books.search') }}?q=subject:poetry" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Poetry</a>
                            </div>
                        </div>
                        <div class="dropdown relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-gray-700 hover:text-indigo-600 font-medium transition flex items-center" @click="open = !open">
                                Non-Fiction
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="dropdown-content" x-cloak x-show="open" x-transition.opacity @click.outside="open = false">
                                <a href="{{ route('books.search') }}?q=subject:history" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">History</a>
                                <a href="{{ route('books.search') }}?q=subject:biography" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Biography</a>
                                <a href="{{ route('books.search') }}?q=subject:memoir" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Memoir</a>
                                <a href="{{ route('books.search') }}?q=subject:self_help" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Self Help</a>
                                <a href="{{ route('books.search') }}?q=subject:business" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Business</a>
                                <a href="{{ route('books.search') }}?q=subject:economics" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Economics</a>
                                <a href="{{ route('books.search') }}?q=subject:health" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Health</a>
                                <a href="{{ route('books.search') }}?q=subject:psychology" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Psychology</a>
                                <a href="{{ route('books.search') }}?q=subject:philosophy" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Philosophy</a>
                                <a href="{{ route('books.search') }}?q=subject:religion" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Religion</a>
                                <a href="{{ route('books.search') }}?q=subject:travel" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Travel</a>
                            </div>
                        </div>
                        <div class="dropdown relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-gray-700 hover:text-indigo-600 font-medium transition flex items-center" @click="open = !open">
                                Academic
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="dropdown-content" x-cloak x-show="open" x-transition.opacity @click.outside="open = false">
                                <a href="{{ route('books.search') }}?q=subject:mathematics" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Mathematics</a>
                                <a href="{{ route('books.search') }}?q=subject:physics" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Physics</a>
                                <a href="{{ route('books.search') }}?q=subject:chemistry" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Chemistry</a>
                                <a href="{{ route('books.search') }}?q=subject:biology" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Biology</a>
                                <a href="{{ route('books.search') }}?q=subject:computer_science" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Computer Science</a>
                                <a href="{{ route('books.search') }}?q=subject:engineering" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Engineering</a>
                                <a href="{{ route('books.search') }}?q=subject:medicine" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Medicine</a>
                                <a href="{{ route('books.search') }}?q=subject:environmental_science" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Environmental Science</a>
                                <a href="{{ route('books.search') }}?q=subject:sociology" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Sociology</a>
                                <a href="{{ route('books.search') }}?q=subject:anthropology" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Anthropology</a>
                                <a href="{{ route('books.search') }}?q=subject:linguistics" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">Linguistics</a>
                            </div>
                        </div>

                        <!-- Search Form -->
                        @auth
                        @php
                            $recentSummaries = \Illuminate\Support\Facades\DB::table('summaries')
                                ->where('user_id', auth()->id())
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-gray-700 hover:text-indigo-600 font-medium transition flex items-center" @click="open = !open">
                                History
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="dropdown-content absolute top-full left-0 bg-white shadow rounded-lg mt-2" x-cloak x-show="open" x-transition.opacity @click.outside="open = false">
                                <div class="w-80 p-3">
                                    @forelse($recentSummaries as $item)
                                        <a href="{{ route('books.show', $item->olid) }}" class="flex items-center gap-3 px-2 py-2 hover:bg-indigo-50 rounded transition">
                                            @if($item->cover_id)
                                                <img src="https://covers.openlibrary.org/b/id/{{ $item->cover_id }}-S.jpg" alt="{{ $item->title }}" class="w-10 h-14 object-cover rounded">
                                            @else
                                                <div class="w-10 h-14 bg-gray-200 rounded"></div>
                                            @endif
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $item->title }}</p>
                                                <p class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($item->summary, 80) }}</p>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-sm text-gray-600 px-2 py-2">No history yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endauth

                        <form action="{{ route('books.search') }}" method="GET" class="flex items-center bg-gray-100 rounded-full px-4 py-2 focus-within:ring-2 focus-within:ring-indigo-500 transition">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" name="q" placeholder="Search books..." class="bg-transparent focus:outline-none text-sm w-48 ml-2" style="border: none;">
                            <button type="submit" class="ml-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-1.5 rounded-full text-sm transition">
                                Search
                            </button>
                        </form>

                        @auth
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-600 text-sm font-medium transition">Login</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-full text-sm font-medium transition shadow-sm">Register</a>
                        @endauth
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center">
                        <button id="menu-btn" class="text-gray-600 hover:text-indigo-600 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-3">
                    <!-- Mobile Categories -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Categories</p>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Fiction</p>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <a href="{{ route('books.search') }}?q=subject:fiction" class="text-sm text-gray-600 hover:text-indigo-600">All Fiction</a>
                                    <a href="{{ route('books.search') }}?q=subject:fantasy" class="text-sm text-gray-600 hover:text-indigo-600">Fantasy</a>
                                    <a href="{{ route('books.search') }}?q=subject:science_fiction" class="text-sm text-gray-600 hover:text-indigo-600">Science Fiction</a>
                                    <a href="{{ route('books.search') }}?q=subject:horror" class="text-sm text-gray-600 hover:text-indigo-600">Horror</a>
                                    <a href="{{ route('books.search') }}?q=subject:historical_fiction" class="text-sm text-gray-600 hover:text-indigo-600">Historical Fiction</a>
                                    <a href="{{ route('books.search') }}?q=subject:classics" class="text-sm text-gray-600 hover:text-indigo-600">Classics</a>
                                    <a href="{{ route('books.search') }}?q=subject:adventure" class="text-sm text-gray-600 hover:text-indigo-600">Adventure</a>
                                    <a href="{{ route('books.search') }}?q=subject:young_adult" class="text-sm text-gray-600 hover:text-indigo-600">Young Adult</a>
                                    <a href="{{ route('books.search') }}?q=subject:children" class="text-sm text-gray-600 hover:text-indigo-600">Children</a>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Non-Fiction</p>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <a href="{{ route('books.search') }}?q=subject:history" class="text-sm text-gray-600 hover:text-indigo-600">History</a>
                                    <a href="{{ route('books.search') }}?q=subject:biography" class="text-sm text-gray-600 hover:text-indigo-600">Biography</a>
                                    <a href="{{ route('books.search') }}?q=subject:memoir" class="text-sm text-gray-600 hover:text-indigo-600">Memoir</a>
                                    <a href="{{ route('books.search') }}?q=subject:self_help" class="text-sm text-gray-600 hover:text-indigo-600">Self Help</a>
                                    <a href="{{ route('books.search') }}?q=subject:business" class="text-sm text-gray-600 hover:text-indigo-600">Business</a>
                                    <a href="{{ route('books.search') }}?q=subject:economics" class="text-sm text-gray-600 hover:text-indigo-600">Economics</a>
                                    <a href="{{ route('books.search') }}?q=subject:health" class="text-sm text-gray-600 hover:text-indigo-600">Health</a>
                                    <a href="{{ route('books.search') }}?q=subject:psychology" class="text-sm text-gray-600 hover:text-indigo-600">Psychology</a>
                                    <a href="{{ route('books.search') }}?q=subject:philosophy" class="text-sm text-gray-600 hover:text-indigo-600">Philosophy</a>
                                    <a href="{{ route('books.search') }}?q=subject:religion" class="text-sm text-gray-600 hover:text-indigo-600">Religion</a>
                                    <a href="{{ route('books.search') }}?q=subject:travel" class="text-sm text-gray-600 hover:text-indigo-600">Travel</a>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">Academic</p>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <a href="{{ route('books.search') }}?q=subject:mathematics" class="text-sm text-gray-600 hover:text-indigo-600">Mathematics</a>
                                    <a href="{{ route('books.search') }}?q=subject:physics" class="text-sm text-gray-600 hover:text-indigo-600">Physics</a>
                                    <a href="{{ route('books.search') }}?q=subject:chemistry" class="text-sm text-gray-600 hover:text-indigo-600">Chemistry</a>
                                    <a href="{{ route('books.search') }}?q=subject:biology" class="text-sm text-gray-600 hover:text-indigo-600">Biology</a>
                                    <a href="{{ route('books.search') }}?q=subject:computer_science" class="text-sm text-gray-600 hover:text-indigo-600">Computer Science</a>
                                    <a href="{{ route('books.search') }}?q=subject:engineering" class="text-sm text-gray-600 hover:text-indigo-600">Engineering</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('books.search') }}" method="GET" class="flex items-center bg-gray-100 rounded-full px-3 py-2">
                        <input type="text" name="q" placeholder="Search books..." class="bg-transparent focus:outline-none text-sm flex-1" style="border: none;">
                        <button type="submit" class="ml-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-3 py-1.5 rounded-full text-sm">Search</button>
                    </form>

                    @auth
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block text-center bg-red-500 hover:bg-red-600 text-white py-2 rounded-full text-sm transition">
                        Logout
                    </a>
                    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="block text-center text-gray-600 hover:text-indigo-600 text-sm">Login</a>
                    <a href="{{ route('register') }}" class="block text-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 rounded-full text-sm">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @if(!Route::is('books.show'))
            <div class="max-w-7xl mx-auto px-6 py-0 mt-6">
                <p class="text-lg">
                    <strong>{{ $pageTitle ?? 'Recently Updated Books' }}</strong>
                </p>
            </div>
            @endif

            @yield('content')
        </main>
        <footer class="bg-white border-t border-gray-200 mt-10">
            <div class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <p class="text-gray-800 font-semibold">About</p>
                    <p class="text-sm text-gray-600 mt-2">AI Powered Book Summaries, explore millions of books from Open Library and instantly generate concise summaries powered by Gemini AI.</p>
                </div>
                <div>
                    <p class="text-gray-800 font-semibold">Fiction</p>
                    <div class="mt-2 space-y-2">
                        <a href="{{ route('books.search') }}?q=subject:fantasy" class="block text-sm text-gray-600 hover:text-indigo-600">Fantasy</a>
                        <a href="{{ route('books.search') }}?q=subject:science_fiction" class="block text-sm text-gray-600 hover:text-indigo-600">Science Fiction</a>
                        <a href="{{ route('books.search') }}?q=subject:horror" class="block text-sm text-gray-600 hover:text-indigo-600">Horror</a>
                        <a href="{{ route('books.search') }}?q=subject:historical_fiction" class="block text-sm text-gray-600 hover:text-indigo-600">Historical Fiction</a>
                        <a href="{{ route('books.search') }}?q=subject:classics" class="block text-sm text-gray-600 hover:text-indigo-600">Classics</a>
                    </div>
                </div>
                <div>
                    <p class="text-gray-800 font-semibold">Non-Fiction</p>
                    <div class="mt-2 space-y-2">
                        <a href="{{ route('books.search') }}?q=subject:history" class="block text-sm text-gray-600 hover:text-indigo-600">History</a>
                        <a href="{{ route('books.search') }}?q=subject:biography" class="block text-sm text-gray-600 hover:text-indigo-600">Biography</a>
                        <a href="{{ route('books.search') }}?q=subject:memoir" class="block text-sm text-gray-600 hover:text-indigo-600">Memoir</a>
                        <a href="{{ route('books.search') }}?q=subject:self_help" class="block text-sm text-gray-600 hover:text-indigo-600">Self Help</a>
                        <a href="{{ route('books.search') }}?q=subject:business" class="block text-sm text-gray-600 hover:text-indigo-600">Business</a>
                    </div>
                </div>
                <div>
                    <p class="text-gray-800 font-semibold">Academic</p>
                    <div class="mt-2 space-y-2">
                        <a href="{{ route('books.search') }}?q=subject:mathematics" class="block text-sm text-gray-600 hover:text-indigo-600">Mathematics</a>
                        <a href="{{ route('books.search') }}?q=subject:physics" class="block text-sm text-gray-600 hover:text-indigo-600">Physics</a>
                        <a href="{{ route('books.search') }}?q=subject:chemistry" class="block text-sm text-gray-600 hover:text-indigo-600">Chemistry</a>
                        <a href="{{ route('books.search') }}?q=subject:biology" class="block text-sm text-gray-600 hover:text-indigo-600">Biology</a>
                        <a href="{{ route('books.search') }}?q=subject:computer_science" class="block text-sm text-gray-600 hover:text-indigo-600">Computer Science</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200">
                <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-500">© {{ date('Y') }} Book Summary</div>
            </div>
        </footer>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Image lazy loading with progress
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.book-cover-wrapper img');

            function markLoaded(img) {
                img.classList.add('loaded');
                const wrapper = img.closest('.book-cover-wrapper');
                if (wrapper) wrapper.classList.add('loaded');
            }

            images.forEach(img => {
                if (img.complete) {
                    markLoaded(img);
                } else {
                    img.addEventListener('load', function() { markLoaded(this); });
                    img.addEventListener('error', function() { markLoaded(this); });
                }
            });
        });

        // Progress bar for page loads
        function showProgress() {
            const progressBar = document.getElementById('progress-bar');
            let width = 0;
            const interval = setInterval(() => {
                if (width >= 90) {
                    clearInterval(interval);
                } else {
                    width += 10;
                    progressBar.style.width = width + '%';
                }
            }, 200);
        }

        window.addEventListener('beforeunload', showProgress);
        window.addEventListener('load', function() {
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 500);
        });
    </script>
</body>

</html>