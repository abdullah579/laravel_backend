<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display published articles for all users.
     */
    public function index(): JsonResponse
    {
        // Check authorization using gate
        if (!Gate::allows('view-published')) {
            return ApiResponse::forbidden('You are not authorized to view articles.');
        }

        $articles = Article::with('author:id,name,email')
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return ApiResponse::paginated($articles, 'Published articles retrieved successfully');
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

        return ApiResponse::paginated($articles, 'Your articles retrieved successfully');
    }

    /**
     * Store a newly created article.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
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

        return ApiResponse::created(
            $article->load('author:id,name,email'),
            'Article created successfully'
        );
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $updateData = $request->only(['title', 'content']);

        // Handle status change
        if ($request->has('status')) {
            $newStatus = $request->status;
            $updateData['status'] = $newStatus;
            $updateData['published_at'] = $newStatus === 'published' ? now() : null;
        }

        $article->update($updateData);

        return ApiResponse::success(
            $article->fresh()->load('author:id,name,email'),
            'Article updated successfully'
        );
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        // Check authorization using policy
        if (!$request->user()->can('delete', $article)) {
            return ApiResponse::forbidden('You are not authorized to delete this article.');
        }

        $article->delete();

        return ApiResponse::success(null, 'Article deleted successfully');
    }

    /**
     * Publish the specified article.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        // Check authorization using policy
        if (!$request->user()->can('publish', $article)) {
            return ApiResponse::forbidden('You are not authorized to publish this article.');
        }

        if ($article->isPublished()) {
            return ApiResponse::success(
                $article->load('author:id,name,email'),
                'Article is already published'
            );
        }

        $article->publish();

        return ApiResponse::success(
            $article->fresh()->load('author:id,name,email'),
            'Article published successfully'
        );
    }
}
