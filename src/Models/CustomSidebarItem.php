<?php

namespace Olivier\CustomButtons\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $label
 * @property string $url
 * @property ?string $icon
 * @property int $sort
 * @property bool $new_tab
 * @property bool $is_active
 */
class CustomSidebarItem extends Model
{
    protected $fillable = [
        'label',
        'url',
        'icon',
        'sort',
        'new_tab',
        'is_active',
        'feature',
    ];

    protected $casts = [
        'new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
