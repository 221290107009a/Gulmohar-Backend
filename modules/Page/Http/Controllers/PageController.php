<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;

class PageController
{
    /**
     * Display page for the slug.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show($slug)
    {
        $logo = File::findOrNew(setting('storefront_header_logo'))->path;
        $page = Page::where('slug', $slug)->firstOrFail();

        return view('storefront::public.pages.show', compact('page', 'logo'));
    }

    public function apiAllPages(){
        $pages = Page::all()->toArray();
        if ($pages) {            
            return response()->json([
                'success' => true,
                'pages' => $pages,
            ]);
        } else{
            return response()->json([
                'success' => false,
                'message' => 'Pages not found',
            ], 404);
        }
    }
    
    public function apiPageDetailUsingSlug($slug){
        $page = Page::where('slug', $slug)->firstOrFail();
        if ($page) {
            
            $page->body = the_content($page->body);
            return response()->json([
                'success' => true,
                'page' => $page,
            ]);
        } else{
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }
    }    
    
}
