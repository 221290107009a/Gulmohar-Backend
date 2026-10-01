<?php

namespace Modules\Storefront\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\HelpCenter\Entities\HelpCategory;
use Modules\HelpCenter\Entities\HelpSubCategory;
use Modules\HelpCenter\Entities\HelpArticle;

class HelpCenterController extends Controller
{
    public function index()
    {
        $categories = HelpCategory::where('is_active', true)
            ->with(['subCategories' => function($q) {
                $q->where('is_active', true)->with(['articles' => function($q) {
                    $q->where('is_active', true);
                }]);
            }, 'articles' => function($q) {
                $q->where('is_active', true)->whereNull('sub_category_id');
            }])
            ->get();

        $helpTree = $categories->map(function($category) {
            $subCategoryItems = $category->subCategories->map(function($subCategory) {
                return (object) [
                    'name' => $subCategory->name,
                    'slug' => $subCategory->slug,
                    'icon' => $subCategory->icon,
                    'type' => 'subcategory',
                    'items' => $subCategory->articles->map(function($article) {
                        return (object) [
                            'name' => $article->title,
                            'slug' => $article->slug,
                            'body' => $article->content,
                            'type' => 'article',
                        ];
                    }),
                ];
            });

            $directArticles = $category->articles->map(function($article) {
                return (object) [
                    'name' => $article->title,
                    'slug' => $article->slug,
                    'body' => $article->content,
                    'type' => 'article',
                ];
            });

            return (object) [
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'items' => $subCategoryItems->concat($directArticles),
                'pages' => [] 
            ];
        });

        $articles = HelpArticle::where('is_active', true)->join('help_article_translations', 'help_articles.id', '=', 'help_article_translations.help_article_id')
            ->where('help_article_translations.locale', locale())
            ->pluck('help_article_translations.content', 'help_articles.slug')
            ->toArray();

        return view('storefront::public.help_center', compact('articles', 'helpTree'));
    }

    public function showArticle($slug)
    {
        $article = HelpArticle::where('slug', $slug)->first();
        if ($article) {
            return $article->content;
        }
        return "Article not found.";
    }
}
