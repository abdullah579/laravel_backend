<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display published articles for all users.
     */
    public function index(Request $request): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('view-published')) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $articles = Article::with('author:id,name,email')
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return response()->json([
            'message' => 'Published articles retrieved successfully',
            'data' => $articles,
        ]);
    }

    /**
     * Display user's own articles.
     */
    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();

        $articles = Article::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'message' => 'Your articles retrieved successfully',
            'data' => $articles,
        ]);
    }

    /**
     * Store a newly created article.
     */
    public function store(Request $request): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('create-article')) {
            return response()->json([
                'message' => 'Unauthorized. You cannot create articles.',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'sometimes|in:draft,published',
        ]);

        $user = $request->user();

        // Authors can only create drafts, editors/admins can publish directly
        $status = $request->status ?? 'draft';
        if ($status === 'published' && !$user->canPublishArticles()) {
            $status = 'draft';
        }

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $status,
            'author_id' => $user->id,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article->load('author:id,name,email'),
        ], 201);
    }

    /**
     * Update the specified article.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        // Check authorization using policy
        if (!$request->user()->can('update', $article)) {
            return response()->json([
                'message' => 'Unauthorized. You cannot edit this article.',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|in:draft,published',
        ]);

        $user = $request->user();
        $updateData = $request->only(['title', 'content']);

        // Handle status change
        if ($request->has('status')) {
            $newStatus = $request->status;

            // Only editors/admins can publish
            if ($newStatus === 'published' && !$user->canPublishArticles()) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot publish articles.',
                ], 403);
            }

            $updateData['status'] = $newStatus;
            $updateData['published_at'] = $newStatus === 'published' ? now() : null;
        }

        $article->update($updateData);

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $article->fresh()->load('author:id,name,email'),
        ]);
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        // Check authorization using policy
        if (!$request->user()->can('delete', $article)) {
            return response()->json([
                'message' => 'Unauthorized. You cannot delete this article.',
            ], 403);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully',
        ]);
    }

    /**
     * Publish the specified article.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        // Check authorization using policy
        if (!$request->user()->can('publish', $article)) {
            return response()->json([
                'message' => 'Unauthorized. You cannot publish this article.',
            ], 403);
        }

        if ($article->isPublished()) {
            return response()->json([
                'message' => 'Article is already published',
                'data' => $article->load('author:id,name,email'),
            ]);
        }

        $article->publish();

        return response()->json([
            'message' => 'Article published successfully',
            'data' => $article->fresh()->load('author:id,name,email'),
        ]);
    }
}
