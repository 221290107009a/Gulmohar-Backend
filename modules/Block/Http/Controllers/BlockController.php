<?php

namespace Modules\Block\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Modules\Block\Entities\Block;
use Modules\Block\Entities\BlockTranslation;
use Modules\Media\Entities\File;
use Modules\Page\Entities\Page;
use App\Admin;


class BlockController
{
    /**
     * Display Block for the slug.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $logo = File::findOrNew(setting('storefront_header_logo'))->path;
        $block = BlockTranslation::where('block_id', $slug)->firstOrFail();
        return view('public.blocks.show', compact('block', 'logo'));
    }

    public function list()
    {
        $block = Block::where('is_active',1)->firstOrFail();
        $curUrl = URL::to('/');   
        
        return view('public.blocks.list', compact('block','curUrl'));   
    }

    public function apiAllBlocks(){
        $blocks = Block::where('is_active',1)->get();
        $baseUrl = URL::to('/');
        foreach ($blocks as $block) {            
            if ($block->content) {
                $block->content = the_content($block->content);
            }
        }
        if ($blocks) {
            return response()->json([
                'success' => true,
                'blocks' => $blocks,
            ], 200);            
        } else{
            return response()->json([
                'success' => false,
                'message' => 'Block not found.',
            ], 401);
        }
    }    
}
