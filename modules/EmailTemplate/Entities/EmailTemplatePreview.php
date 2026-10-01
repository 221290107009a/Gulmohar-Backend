<?php

namespace Modules\EmailTemplate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmailTemplate\Database\factories\EmailTemplatePreviewFactory;

class EmailTemplatePreview extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'content', 'preview_url'];
    
    protected static function newFactory(): EmailTemplatePreviewFactory
    {
        //return EmailTemplatePreviewFactory::new();
    }
}
