<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BookController extends Controller
{
    public function index()
    {
        try {
            $books = Cache::remember('trending_daily', 600, function () {
                return Http::get('https://openlibrary.org/trending/daily.json')
                    ->json()['works'] ?? [];
            });
        } catch (\Exception $e) {
            $books = [];
        }

        $pageTitle = 'Recently Updated Books';

        return view('books.index', compact('books', 'pageTitle'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query) return redirect()->route('dashboard');

        try {
            $books = Cache::remember('search_' . md5($query), 600, function () use ($query) {
                return Http::get('https://openlibrary.org/search.json', ['q' => $query])
                    ->json()['docs'] ?? [];
            });
        } catch (\Exception $e) {
            $books = [];
        }

        if (preg_match('/^subject:(.+)$/', $query, $m)) {
            $subject = ucwords(str_replace('_', ' ', $m[1]));
            $pageTitle = "Category: {$subject}";
        } else {
            $pageTitle = "Results for: {$query}";
        }

        return view('books.index', compact('books', 'pageTitle'));
    }

    public function show($olid)
    {
        try {
            $book = Http::get("https://openlibrary.org/works/{$olid}.json")->json();

            // Fetch authors efficiently using cache to reduce multiple calls
            if (!empty($book['authors']) && is_array($book['authors'])) {
                $book['authors'] = array_map(function ($authorEntry) {
                    $authorKey = basename($authorEntry['author']['key'] ?? '');
                    if (!$authorKey) {
                        $authorEntry['author']['name'] = 'Unknown';
                        return $authorEntry;
                    }

                    $authorEntry['author']['name'] = Cache::remember("author_{$authorKey}", 3600, function() use ($authorKey) {
                        $resp = Http::get("https://openlibrary.org/authors/{$authorKey}.json");
                        return $resp->successful() ? $resp->json()['name'] ?? 'Unknown' : 'Unknown';
                    });

                    return $authorEntry;
                }, $book['authors']);
            } else {
                $book['authors'] = [];
            }

            $rawDesc = $book['description'] ?? null;
            $descriptionText = is_array($rawDesc) ? ($rawDesc['value'] ?? null) : (is_string($rawDesc) ? $rawDesc : null);

            $existingSummary = null;
            if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('summaries')) {
                $existingSummary = DB::table('summaries')
                    ->where('user_id', Auth::id())
                    ->where('olid', $olid)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            return view('books.show', compact('book', 'olid', 'descriptionText', 'existingSummary'));
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'An error occurred while fetching book details']);
        }
    }

    public function generateSummary(Request $request, $olid)
    {
        try {
            $book = Http::get("https://openlibrary.org/works/{$olid}.json")->json();

            $title = $book['title'] ?? 'Untitled';
            $description = is_array($book['description'])
                ? ($book['description']['value'] ?? '')
                : ($book['description'] ?? '');
            $authors = [];
            if (!empty($book['authors']) && is_array($book['authors'])) {
                $authors = array_map(function ($a) { return $a['author']['name'] ?? null; }, $book['authors']);
                $authors = array_filter($authors);
            }
            $coverId = $book['covers'][0] ?? null;

            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                Log::error('Gemini API key not configured');
                return back()->withErrors(['msg' => 'Gemini API key not configured']);
            }

            $prompt = "Title: {$title}\n" . (count($authors) ? "Author(s): " . implode(', ', $authors) . "\n" : "") .
                "Description: " . ($description ?: 'No description available.') . "\n\n" .
                "Generate a comprehensive, well-structured summary of approximately 1000 words. \n" .
                "Use clear paragraphs and cover main themes, key ideas, and notable insights. Avoid spoilers beyond the general arc.";

            $geminiResponse = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[ 'text' => $prompt ]],
                    ]],
                ]);

            $summary = null;
            if ($geminiResponse->successful()) {
                $body = $geminiResponse->json();
                $summary = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            $summary = $summary ? preg_replace('/\*\*|##/', '', $summary) : null;

            if (!$summary) {
                $bodyJson = $geminiResponse->json();
                $rawBody = method_exists($geminiResponse, 'body') ? $geminiResponse->body() : null;
                Log::error('Gemini summary generation failed', [
                    'status' => $geminiResponse->status(),
                    'olid' => $olid,
                    'title' => $title,
                    'response_json' => $bodyJson,
                    'response_body' => $rawBody,
                ]);
                $apiErr = is_array($bodyJson) ? ($bodyJson['error']['message'] ?? null) : null;
                $err = $apiErr ?: ($geminiResponse->successful() ? 'Summary generation failed.' : ('Failed to generate summary: ' . $geminiResponse->status()));
                if ($request->expectsJson()) {
                    return response()->json(['error' => $err], $geminiResponse->successful() ? 422 : $geminiResponse->status());
                }
                return back()->withErrors(['msg' => $err]);
            }

            DB::table('summaries')->insert([
                'user_id' => Auth::id(),
                'olid' => $olid,
                'title' => $title,
                'cover_id' => $coverId,
                'summary' => $summary,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['summary' => $summary]);
            }
            return back()->with('summary', $summary);
        } catch (\Exception $e) {
            Log::error('Gemini summary exception', [
                'olid' => $olid ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Error generating summary'], 500);
            }
            return back()->withErrors(['msg' => 'Error generating summary']);
        }
    }

    public function history(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $pageTitle = 'Your History';
            $summaries = DB::table('summaries')
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('summaries.index', compact('summaries', 'pageTitle'));
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Unable to load history']);
        }
    }

    public function clearHistory(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (Schema::hasTable('summaries')) {
            DB::table('summaries')->where('user_id', Auth::id())->delete();
        }
        return redirect()->route('summaries.index')->with('status', 'History cleared');
    }
}
