<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Powered Book Summaries</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50 text-gray-900">

    <!-- Header Navbar -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
            <h1 class="text-2xl font-bold text-indigo-600">Book Summary</h1>
            <nav class="space-x-4">
                <a href="{{ route('login') }}" class="px-4 py-2 text-indigo-600 font-semibold hover:underline">Login</a>
                <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow">Sign Up</a>
            </nav>
        </div>
    </header>

    
    <!-- Hero Section -->
    <section class="relative h-[400px] flex items-center justify-center text-center">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center" 
             style="background-image: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1920&q=80');">
        </div>
        <div class="absolute inset-0 bg-black/60"></div> <!-- Overlay -->

        <!-- Content -->
        <div class="relative z-10 text-white px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Discover Books & Unlock <span class="text-indigo-300">AI Summaries</span>
            </h2>
            <p class="text-lg md:text-xl max-w-2xl mx-auto mb-6">
                With <strong>Our App</strong>, explore millions of books from Open Library and instantly generate 
                concise summaries powered by Gemini AI.
            </p>
            <a href="{{ route('login') }}" 
               class="px-6 py-3 bg-indigo-600 text-white rounded-lg text-lg font-medium hover:bg-indigo-700 shadow-lg">
                Get Started Free
            </a>
        </div>
    </section>


    <!-- Features -->
    <section class="py-16 bg-gray-100 px-6">
        <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-10 text-center">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h3 class="text-xl font-semibold mb-3">Explore Books</h3>
                <p class="text-gray-600">Search and browse over 28 Million books</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h3 class="text-xl font-semibold mb-3">AI Summaries</h3>
                <p class="text-gray-600">Generate instant book summaries using AI.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-6 bg-white shadow">
        <p class="text-gray-500 text-sm">© {{ date('Y') }} Readora. All rights reserved.</p>
    </footer>
</body>

</html>