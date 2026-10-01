<?php

namespace Modules\Service\Services\Sections;

use Modules\Community\Entities\SponsoredPost;
use Modules\Service\Services\Sections\Contracts\SectionHandlerInterface;
use Illuminate\Support\Facades\Log;

/**
 * Sponsored Post Section Handler
 * 
 * Handles fetching and transforming sponsored posts for the home screen
 */
class SponsoredPostSectionHandler implements SectionHandlerInterface
{
    /**
     * @var string Default logo to use if none is found
     */
    private $defaultLogo = 'https://sanghoapp.phxsolution.com/storage/media/Q3VADHg3yRjRROIefpvOPxynajeO72KYwICatO6b.png';

    /**
     * {@inheritdoc}
     */
    public function handle(string $sectionId, ?int $userId = null): ?array
    {
        $sponsoredPosts = SponsoredPost::active()
            ->with([
                'sponsoredProgramPost.program.community',
                'sponsoredBusinessPost.business',
                'sponsoredProductsPost',
                'sponsoredTouristPlacePost.touristPlace',
                'sponsoredQuotePost.templateCategory'
            ])
            ->orderBy('priority', 'asc')
            ->get();

        if ($sponsoredPosts->isEmpty()) {
            return null;
        }

        $items = [];
        foreach ($sponsoredPosts as $post) {
            $transformed = $this->transformPost($post);
            if ($transformed) {
                $items[] = $transformed;
            }
        }

        if (empty($items)) {
            return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => [
                'title' => 'Sponsored',
                'items' => $items,
            ],
        ];
    }

    /**
     * Create a specific sponsored posts section for a given type
     */
    public function createSpecificSponsoredPosts(string $sectionId, ?int $userId, string $type): ?array
    {
        $sponsoredPosts = SponsoredPost::active()
            ->where('type', $type)
            ->with([
                'sponsoredProgramPost.program.community',
                'sponsoredBusinessPost.business',
                'sponsoredProductsPost',
                'sponsoredTouristPlacePost.touristPlace',
                'sponsoredQuotePost.templateCategory'
            ])
            ->orderBy('priority', 'asc')
            ->get();

        if ($sponsoredPosts->isEmpty()) {
            return null;
        }

        $items = [];
        foreach ($sponsoredPosts as $post) {
            $transformed = $this->transformPost($post);
            if ($transformed) {
                $items[] = $transformed;
            }
        }

        if (empty($items)) {
            return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'service_type' => $type,
            'data' => [
                'title' => 'Sponsored',
                'items' => $items,
            ],
        ];
    }

    /**
     * Create a section with a single sponsored post at a specific offset
     */
    public function handleSingle(string $sectionId, ?int $userId, int $offset): ?array
    {
        $post = SponsoredPost::active()
            ->with([
                'sponsoredProgramPost.program.community',
                'sponsoredBusinessPost.business',
                'sponsoredProductsPost',
                'sponsoredTouristPlacePost.touristPlace',
                'sponsoredQuotePost.templateCategory'
            ])
            ->orderBy('priority', 'asc')
            ->offset($offset)
            ->limit(1)
            ->first();

        if (!$post) {
            return null;
        }

        $transformed = $this->transformPost($post);
        if (!$transformed) {
            return null;
        }

        return [
            'id' => $sectionId,
            'type' => $this->getSectionType(),
            'data' => [
                'title' => 'Sponsored',
                'items' => [$transformed],
            ],
        ];
    }

    /**
     * Get count of active sponsored posts
     */
    public function getActiveCount(): int
    {
        return SponsoredPost::active()->count();
    }
    private function transformPost(SponsoredPost $post): ?array
    {
        $sponsorData = $this->getSponsorData($post);
        
        // Match the frontend's SponsoredPost interface
        return [
            'id' => $post->id,
            'user_name' => $sponsorData['title'] ?? 'Sangho App',
            'user_profile' => $sponsorData['logo'] ?? $this->defaultLogo,
            'sponsor_name' => $sponsorData['title'] ?? 'Sponsored',
            'sponsor_type' => $post->type,
            'content' => $sponsorData['content'] ?? '',
            'images' => is_array($sponsorData['images']) ? $sponsorData['images'] : [],
            'video_url' => null, // Not currently stored in sponsored_posts
            'sponsor_footer_link' => $post->footer_button_url,
            'sponsor_footer_title' => $post->footer_content,
            'sponsor_footer_btn_text' => $post->footer_button_text,
            'extra_data' => $sponsorData['extra_data'] ?? [],
        ];
    }

    /**
     * Helper to extract detailed sponsor data based on type
     */
    private function getSponsorData(SponsoredPost $post): array
    {
        $data = [
            'title' => 'Sangho App',
            'name' => 'Sponsored',
            'logo' => $this->defaultLogo,
            'images' => [],
            'content' => '',
            'extra_data' => []
        ];

        switch ($post->type) {
            case 'program':
                if ($post->sponsoredProgramPost && $post->sponsoredProgramPost->program) {
                    $program = $post->sponsoredProgramPost->program;
                    $data['title'] = $program->name ?? 'Program';
                    $data['name'] = 'Program';
                    if ($program->community && $program->community->image) {
                        $data['logo'] = asset('storage/community/' . $program->community->image);
                    }
                    if ($program->logo && $program->logo->path) {
                        $data['images'][] = url($program->logo->path);
                    }
                    $data['content'] = $program->description ?? '';
                    $data['extra_data'] = [
                        'id' => $program->id,
                        'name' => $program->name,
                        'address' => $program->address,
                    ];
                }
                break;

            case 'business':
                if ($post->sponsoredBusinessPost && $post->sponsoredBusinessPost->business) {
                    $business = $post->sponsoredBusinessPost->business;
                    $data['title'] = $business->title ?? 'Business';
                    $data['name'] = 'Business';
                    if ($business->media) {
                        $mediaStr = $business->media;
                        $mediaImages = explode(',', $mediaStr);
                        if (!empty($mediaImages)) {
                            foreach ($mediaImages as $image) {
                                $data['images'][] = url('storage/business_media/' . trim($image));
                            }
                            $data['logo'] = $data['images'][0];
                        }
                    }
                    $data['content'] = $business->description ?? '';
                    $data['extra_data'] = [
                        'id' => $business->id,
                        'title' => $business->title,
                        'address' => $business->address,
                    ];
                }
                break;

            case 'products':
                if ($post->sponsoredProductsPost) {
                    $data['title'] = 'Products';
                    $data['name'] = 'Products';
                    
                    $sellerId = $post->sponsoredProductsPost->seller_id;
                    if ($sellerId) {
                        $seller = \Modules\Seller\Entities\Seller::find($sellerId);
                        if ($seller) {
                            $data['title'] = $seller->shop_name ?? 'Products';
                            if ($seller->logo && $seller->logo->path) {
                                $data['logo'] = $seller->logo->path;
                            } elseif ($seller->image && $seller->image->path) {
                                $data['logo'] = $seller->image->path;
                            }
                        }
                    }
                    
                    $products = $post->sponsoredProductsPost->products();
                    if ($products && $products->isNotEmpty()) {
                        $productData = [];
                        foreach ($products as $product) {
                            $productData[] = [
                                'product_id' => $product->id,
                                'product_slug' => $product->slug,
                                'product_name' => $product->name ?? '',
                                'product_image' => $product->base_image ? $product->base_image->path : null,
                                'product_price' => $product->price ? $product->price->amount() : 0
                            ];
                        }
                        $data['extra_data'] = $productData;
                    }
                }
                break;

            case 'tourist_place':
                if ($post->sponsoredTouristPlacePost && $post->sponsoredTouristPlacePost->touristPlace) {
                    $place = $post->sponsoredTouristPlacePost->touristPlace;
                    $data['title'] = $place->name ?? 'Tourist Place';
                    $data['name'] = 'Tourist Place';
                    if ($place->logo && $place->logo->path) {
                        $data['logo'] = $place->logo->path;
                        $data['images'][] = $place->logo->path;
                    }
                    $data['content'] = $place->description ?? '';
                    $data['extra_data'] = [
                        'id' => $place->id,
                        'name' => $place->name,
                        'address' => $place->address,
                    ];
                }
                break;

            case 'quote':
                if ($post->sponsoredQuotePost) {
                    $quotePost = $post->sponsoredQuotePost;
                    $category = $quotePost->templateCategory;
                    $data['title'] = $category ? $category->name : 'Quote Templates';
                    $data['name'] = 'Quote';
                    
                    $templates = $quotePost->templates();
                    if ($templates && $templates->isNotEmpty()) {
                        $templateData = [];
                        foreach ($templates as $template) {
                            $templateData[] = [
                                'template_id' => $template->id,
                                'template_image' => $template->logo ? $template->logo->path : null,
                            ];
                        }
                        $data['extra_data'] = [
                            'category_id' => $quotePost->template_category_id,
                            'category_name' => $category ? $category->name : '',
                            'templates' => $templateData
                        ];
                    }
                }
                break;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionType(): string
    {
        return 'sponsored-posts';
    }
}
