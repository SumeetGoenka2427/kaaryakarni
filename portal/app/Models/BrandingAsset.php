<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** An uploaded logo / favicon / share image. Read through BrandingService (cached). */
class BrandingAsset extends Model
{
    protected $fillable = ['type', 'path', 'width', 'height', 'version'];

    /** Public URL with a version query so browsers fetch a replaced logo immediately. */
    public function url(): string
    {
        return Storage::disk('public')->url($this->path).'?v='.$this->version;
    }
}
