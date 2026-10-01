<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Category\Entities\Category;
use Illuminate\Http\Request;

class CategoryController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('storefront::public.categories.index', [
            'categories' => Category::all()->nest(),
        ]);
    }

    /**
     * Get only parent categories (categories with no parent)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentCategoriesApi()
    {
        try {
            $categories = Category::with('translations')
                ->where('is_active', 1)
                ->whereNull('parent_id')
                ->get();

            $categoryMap = [];
            foreach ($categories as $category) {
                $categoryMap[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'position' => $category->position,
                ];
            }

            return response()->json([
                'status' => 'success',
                'categories' => $categoryMap
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch parent categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories by parent category ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcategoriesById($id)
    {
        try {
            $subcategories = Category::with('translations')
                ->where('parent_id', $id)
                ->where('is_active', 1)
                ->get();

            $subcategoryMap = [];
            foreach ($subcategories as $category) {
                $subcategoryMap[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'position' => $category->position,
                ];
            }

            return response()->json([
                'status' => 'success',
                'subcategories' => $subcategoryMap
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of all categories in hierarchical structure
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryListApi()
    {
        try {
            $categories = Category::with('translations')
                ->where('is_active', 1)
                ->get();
            
            $categoryMap = [];

            foreach ($categories as $category) {
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                    'position' => $category->position,
                    'is_searchable' => $category->is_searchable,
                    'is_active' => $category->is_active,
                    'logo' => optional($category->logo)->path,
                    'banner' => optional($category->banner)->path,
                    'children' => []
                ];
                $categoryMap[] = $categoryData;
            }

            return response()->json([
                'status' => 'success',
                'categories' => $categoryMap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function onlineStoreCategoryListApi()
    {
        try {
            $categories = Category::with('translations')
                ->where('is_active', 1)
                ->get();

            $categoryMap = [];

            foreach ($categories as $category) {
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                    'position' => $category->position,
                    'is_searchable' => $category->is_searchable,
                    'is_active' => $category->is_active,
                    'logo' => optional($category->logo)->path,
                    'banner' => optional($category->banner)->path,
                    'children' => []
                ];
                $categoryMap[] = $categoryData;
            }

            return response()->json([
                'status' => 'success',
                'categories' => $categoryMap
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories by parent category ID
     *
     * @param int $id Parent category ID
     * @return JsonResponse
     */
    public function getSubcategoriesBySlug(Request $request)
    {
        $slug = $request->input('slug');
        try {
            // First find the parent category by slug
            $parentCategory = Category::where('slug', $slug)->first();
            
            if (!$parentCategory) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ], 404);
            }

            // Then get all active child categories
            $subcategories = Category::with('translations')
                ->where('parent_id', $parentCategory->id)
                ->where('is_active', 1)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'position' => $category->position,
                        'is_searchable' => $category->is_searchable,
                        'is_active' => $category->is_active,
                        'logo' => $category->logo->path,
                        'banner' => $category->banner->path
                    ];
                });

            return response()->json([
                'status' => 'success',
                'subcategories' => $subcategories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
