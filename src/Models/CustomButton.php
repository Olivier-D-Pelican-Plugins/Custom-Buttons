<?php

namespace Olivier\CustomButtons\Models;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property ?int $server_id
 * @property string $text
 * @property string $url
 * @property ?string $icon
 * @property string $color
 * @property bool $new_tab
 * @property int $sort
 * @property bool $is_active
 */
class CustomButton extends Model
{
    protected $fillable = [
        'server_id',
        'text',
        'url',
        'icon',
        'color',
        'new_tab',
        'sort',
        'is_active',
        'feature',
    ];

    protected $casts = [
        'server_id' => 'integer',
        'new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForServer($query, int $serverId)
    {
        return $query->where('server_id', $serverId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('server_id');
    }
}
