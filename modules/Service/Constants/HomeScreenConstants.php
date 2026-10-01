<?php

namespace Modules\Service\Constants;

class HomeScreenConstants
{
    const PER_PAGE_DEFAULT = 24;
    const PER_PAGE_MIN = 1;
    const PER_PAGE_MAX = 50;
    
    // Session management
    const SESSION_CACHE_TTL = 86400; // 24 hours in seconds
    const SESSION_ID_LENGTH = 64; // SHA-256 hash length
    
    // Infinite scroll behavior
    const AUTO_CYCLE_RESTART = true; // Restart with new shuffle when content exhausted
    const MIN_SECTIONS_BEFORE_CYCLE = 20; // Minimum unique sections required
}
