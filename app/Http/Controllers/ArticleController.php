<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('acode', 'like', "%{$search}%");
            });
        }
        
        $articles = $query->orderBy('name')->paginate(15)->appends($request->query());
        $editingArticle = null;

        if ($request->has('edit')) {
            $editingArticle = Article::find($request->edit);
        }

        return view('articles.index', compact('articles', 'editingArticle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'acode' => ['required', 'string', 'max:50', 'unique:articles,acode'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['acode'])) $data['acode'] = strtoupper($data['acode']);

        Article::create($data);

        return redirect()
            ->route('articles.index')
            ->with('status', 'Article created.');
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'acode' => ['required', 'string', 'max:50', 'unique:articles,acode,' . $article->id],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['acode'])) $data['acode'] = strtoupper($data['acode']);

        $article->update($data);

        return redirect()
            ->route('articles.index')
            ->with('status', 'Article updated.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Article deleted.');
    }
}
