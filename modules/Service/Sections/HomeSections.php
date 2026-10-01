<?php

namespace Modules\Service\Sections;

use Illuminate\Support\Str;
use Modules\Community\Entities\Community;
use Modules\Community\Entities\CommunityMembers;
use Modules\Product\Entities\Product;
use Modules\Business\Entities\Business;
use Modules\Business\Entities\BusinessReview;
use Modules\Business\Entities\UserBusinessLikes;
use Modules\Template\Entities\Template;
use Modules\Badge\Entities\Badge;
use Modules\ProgramNotification\Entities\ProgramNotification;
use Modules\ProgramNotification\Entities\ProgramRegistration;
use Modules\BiodataFrame\Entities\BiodataFrame;
use Modules\AudioSong\Entities\AudioSongAlbum;
use Modules\Audiobook\Entities\Audiobook;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Base Section Abstract Class
 */
abstract class BaseSection
{
    abstract public function getData(?int $userId = null): array;
    abstract public function getType(): string;

    public function format(?string $id = null, ?int $userId = null): array
    {
        return [
            'id' => $id ?? ($this->getType() . '_' . (string) Str::uuid()),
            'type' => $this->getType(),
            'data' => $this->getData($userId),
        ];
    }
}

/**
 * Trait for fetching program-related data
 */
trait HandlesProgramData
{
    protected function getProgramNotifications(?int $userId): array
    {
        try {
            $programs = ProgramNotification::where('is_active', 1)
                ->where(function($query) {
                    $query->whereDate('start_date', '>=', Carbon::today())
                        ->orWhereDate('end_date', '>=', Carbon::today());
                })
                ->with(['community'])
                ->latest()
                ->limit(10)
                ->get();
            
            $programsData = [];
            foreach ($programs as $program) {
                $is_registered = 0;
                $is_attended = 0;
                $attended_at = null;
                $is_paid = 0;

                if ($userId) {
                    $registration = ProgramRegistration::where('program_notification_id', $program->id)
                        ->where('user_id', $userId)
                        ->first();
                    
                    if ($registration) {
                        $is_registered = 1;
                        $is_attended = $registration->is_attended ? 1 : 0;
                        $attended_at = $registration->attended_at;
                        $is_paid = $registration->is_paid ? 1 : 0;
                    }
                }

                $programsData[] = [
                    'id' => $program->id,
                    'name' => $program->name ?? $program->title,
                    'title' => $program->title,
                    'description' => $program->description,
                    'banner' => $program->logo->path ?? '',
                    'donation_qr_image' => $program->image->path ?? '',
                    'start_date' => $program->start_date,
                    'end_date' => $program->end_date,
                    'start_time' => $program->start_time,
                    'end_time' => $program->end_time,
                    'start_date_time' => $program->start_date . ' ' . $program->start_time,
                    'end_date_time' => $program->end_date . ' ' . $program->end_time,
                    'location' => $program->location,
                    'address' => $program->address ?? $program->location,
                    'latitude' => $program->latitude,
                    'longitude' => $program->longitude,
                    'registration_required' => $program->registration_required,
                    'registration_type' => $program->registration_type,
                    'registration_amount' => $program->registration_amount,
                    'is_registered' => $is_registered,
                    'is_attended' => $is_attended,
                    'attended_at' => $attended_at,
                    'is_paid' => $is_paid,
                    'total_registered' => ProgramRegistration::where('program_notification_id', $program->id)->count(),
                    'total_attended' => ProgramRegistration::where('program_notification_id', $program->id)->where('is_attended', 1)->count(),
                    'community' => [
                        'id' => $program->community->id ?? null,
                        'name' => $program->community->name ?? null,
                    ],
                ];
            }
            
            return ['programs' => $programsData];
        } catch (\Exception $e) {
            Log::error('Error in getProgramNotifications: ' . $e->getMessage());
            return ['programs' => []];
        }
    }
}

/**
 * Trait for fetching template and quote data
 */
trait HandlesTemplateData
{
    protected function getHomeTemplates(): array
    {
        try {
            $templates = Template::with(['templateCategory'])
                ->where('is_active', 1)
                ->whereHas('templateCategory', function($q) {
                    $q->where('is_active', 1);
                })
                ->inRandomOrder()
                ->limit(10)
                ->get();
            
            foreach ($templates as $template) {
                if ($template->logo && $template->logo->path) {
                    $template->template_url = $template->logo->path;
                }
                
                if (!empty($template->badge_id)) {
                    $badge = Badge::find($template->badge_id);
                    if ($badge && $badge->logo && $badge->logo->path) {
                        $template->badge_url = $badge->logo->path;
                    }
                }
                
                $template->share_message = '';
                if (isset($template->message) && $template->message != '') {
                    if ($template->is_default_message) {
                        if (isset($template->templateCategory->message) && $template->templateCategory->message != '') {
                            if ($template->templateCategory && $template->templateCategory->is_default_message) {
                                $template->share_message = setting('quote_share_message');
                            } else {
                                $template->share_message = $template->templateCategory->message;
                            }
                        } else {
                            $template->share_message = setting('quote_share_message');
                        }
                    } else {
                        $template->share_message = $template->message;
                    }
                } elseif (isset($template->templateCategory->message) && $template->templateCategory->message != '') {
                    if ($template->templateCategory && $template->templateCategory->is_default_message) {
                        $template->share_message = setting('quote_share_message');
                    } else {
                        $template->share_message = $template->templateCategory->message;
                    }
                } else {
                    $template->share_message = setting('quote_share_message');
                }
                
                unset($template->templateCategory);
            }
            
            return ['templates' => $templates->toArray()];
        } catch (\Exception $e) {
            return ['templates' => []];
        }
    }
}

/**
 * Concrete Section Implementations
 */

class DailyHighlightsSection extends BaseSection
{
    public function getType(): string { return 'today-on-sangho'; }
    public function getData(?int $userId = null): array { return []; }
}

class QuoteSection extends BaseSection
{
    use HandlesTemplateData;
    public function getType(): string { return 'quote'; }
    public function getData(?int $userId = null): array { return $this->getHomeTemplates()['templates'] ?? []; }
}

class AdBannerSection extends BaseSection
{
    public function getType(): string { return 'ad-banner'; }
    public function getData(?int $userId = null): array { return []; }
}

class GridListingSection extends BaseSection
{
    public function getType(): string { return 'grid-listing'; }
    public function getData(?int $userId = null): array
    {
        $communities = Community::where('is_active', true)->latest()->limit(10)->get();
        return $communities->map(function ($community) use ($userId) {
                $membersCount = CommunityMembers::where('community_id', $community->id)->where('is_active', 1)->where('approval_status', 'approved')->count();
                $isJoined = $userId ? (CommunityMembers::where('community_id', $community->id)->where('user_id', $userId)->where('is_active', 1)->where('approval_status', 'approved')->exists() ? 1 : 0) : 0;
                return [
                    'id' => $community->id,
                    'name' => $community->name,
                    'description' => $community->description,
                    'category' => $community->category,
                    'image' => $community->logo->path ?? '',
                    'total_joined_members' => $membersCount,
                    'members_count' => $membersCount,
                    'is_active' => $community->is_active,
                    'is_joined' => $isJoined,
                    'is_verified' => $community->is_verified ?? 0,
                ];
            })->toArray();
    }
}

class ProductGridSection extends BaseSection
{
    public function getType(): string { return 'product-grid'; }
    public function getData(?int $userId = null): array
    {
        try {
            $products = Product::where('is_active', 1)->whereHas('categories', function ($q) { $q->where('parent_id', 10); })
                ->with('translations', 'attributes.attribute.attributeSet')->latest()->limit(10)->get();
            return $products->toArray();
        } catch (\Exception $e) { return []; }
    }
}

class BusinessCardsSection extends BaseSection
{
    public function getType(): string { return 'business-cards'; }
    public function getData(?int $userId = null): array
    {
        try {
            $businesses = Business::where('is_active', 1)->latest()->limit(10)->get();
            foreach ($businesses as $business) {
                $mediaItems = [];
                $mediaIds = explode(',', $business->media);
                foreach ($mediaIds as $mediaId) { if (!empty($mediaId)) { $mediaItems[] = Storage::url('business_media/' . $mediaId); } }
                $business->media_urls = $mediaItems;
                $reviews = BusinessReview::where(['business_id' => $business->id, 'status' => 'verified'])->get();
                $business->total_reviews = $reviews->count();
                $business->average_rating = $reviews->avg('rating') ?? 0;
                $business->like_count = UserBusinessLikes::where('business_id', $business->id)->count();
            }
            return $businesses->toArray();
        } catch (\Exception $e) { return []; }
    }
}

class HeroCarouselSection extends BaseSection
{
    use HandlesProgramData;
    public function getType(): string { return 'hero-carousel'; }
    public function getData(?int $userId = null): array { return $this->getProgramNotifications($userId)['programs'] ?? []; }
}

class BiodataCarouselSection extends BaseSection
{
    public function getType(): string { return 'biodata-carousel'; }
    public function getData(?int $userId = null): array
    {
        try {
            $biodataFrames = BiodataFrame::where('is_active', 1)->limit(10)->get();
            foreach ($biodataFrames as $frame) {
                if ($frame->logo && $frame->logo->path) { $frame->frame_url = $frame->logo->path; }
                if ($frame->list_image && $frame->list_image->path) { $frame->fill_frame_url = $frame->list_image->path; }
            }
            return $biodataFrames->toArray();
        } catch (\Exception $e) { return []; }
    }
}

class AlbumQuickPicksSection extends BaseSection
{
    public function getType(): string { return 'album-quick-picks'; }
    public function getData(?int $userId = null): array { return []; }
}

class TemplateGridSection extends BaseSection
{
    use HandlesTemplateData;
    public function getType(): string { return 'template-grid'; }
    public function getData(?int $userId = null): array { return $this->getHomeTemplates()['templates'] ?? []; }
}

class ExpandableCardsSection extends BaseSection
{
    public function getType(): string { return 'expandable-cards'; }
    public function getData(?int $userId = null): array
    {
        try {
            $albums = AudioSongAlbum::where('is_active', 1)->latest()->limit(3)->get();
            $songItems = [];
            foreach ($albums as $album) {
                $tracks = $album->tracks()->limit(3)->get();
                $listItems = [];
                foreach ($tracks as $track) {
                    $listItems[] = [
                        'id' => $track->id, 'title' => $track->title, 'subtitle' => $album->title, 'image' => $album->cover_image ?? '',
                    ];
                }
                $songItems[] = [
                    'id' => 'song-' . $album->id, 'title' => $album->title, 'description' => 'Trending Songs',
                    'stats' => $album->tracks()->count() . ' tracks', 'gridImages' => [$album->cover_image ?? ''], 'listItems' => $listItems
                ];
            }
            $audiobooks = Audiobook::where('is_active', 1)->latest()->limit(3)->get();
            $bookItems = [];
            foreach ($audiobooks as $book) {
                $chapters = $book->chapters()->limit(3)->get();
                $listItems = [];
                foreach ($chapters as $chapter) {
                    $listItems[] = [
                        'id' => $chapter->id, 'title' => $chapter->title, 'subtitle' => $book->name, 'image' => $book->logo->path ?? '',
                    ];
                }
                $bookItems[] = [
                    'id' => 'book-' . $book->id, 'title' => $book->name, 'description' => 'Top picks for you',
                    'stats' => $book->chapters()->count() . ' chapters', 'gridImages' => [$book->logo->path ?? ''], 'listItems' => $listItems
                ];
            }
            return array_merge($songItems, $bookItems);
        } catch (\Exception $e) { Log::error('Error in ExpandableCardsSection: ' . $e->getMessage()); return []; }
    }
}

class ModernCollageSection extends BaseSection
{ 
    use HandlesTemplateData; 
    public function getType(): string { return 'modern-collage'; } 
    public function getData(?int $userId = null): array { return $this->getHomeTemplates()['templates'] ?? []; } 
}
class BiodataGallerySection extends BaseSection
{ 
    public function getType(): string { return 'biodata-gallery'; } 
    public function getData(?int $userId = null): array { return []; } 
}
class EventTimelineSection extends BaseSection
{ 
    use HandlesProgramData; 
    public function getType(): string { return 'event-timeline'; } 
    public function getData(?int $userId = null): array { return $this->getProgramNotifications($userId)['programs'] ?? []; } 
}
class EventCardSection extends BaseSection
{ 
    use HandlesProgramData; 
    public function getType(): string { return 'event-card'; } 
    public function getData(?int $userId = null): array { return $this->getProgramNotifications($userId)['programs'] ?? []; } 
}
class DualRowScrollSection extends BaseSection
{ 
    public function getType(): string { return 'dual-row-scroll'; } 
    public function getData(?int $userId = null): array { return []; } 
}
class InfoCardsSection extends BaseSection
{ 
    public function getType(): string { return 'info-cards'; } 
    public function getData(?int $userId = null): array { return []; } 
}
class InviteFriendsSection extends BaseSection
{ 
    public function getType(): string { return 'invite-friends'; } 
    public function getData(?int $userId = null): array { return []; } 
}
class MiniPlayerSection extends BaseSection
{ 
    public function getType(): string { return 'mini-player'; } 
    public function getData(?int $userId = null): array { return []; } 
}
class FooterSpaceSection extends BaseSection
{ 
    public function getType(): string { return 'footer-space'; } 
    public function getData(?int $userId = null): array { return []; } 
}
