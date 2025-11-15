<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $database_server_id
 * @property string $volume_id
 * @property string $recurrence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DatabaseServer $databaseServer
 * @property-read \App\Models\Volume $volume
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereDatabaseServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereRecurrence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Backup whereVolumeId($value)
 *
 * @mixin \Eloquent
 */
class Backup extends Model
{
    use HasUlids;

    protected $fillable = [
        'database_server_id',
        'volume_id',
        'recurrence',
    ];

    public function databaseServer(): BelongsTo
    {
        return $this->belongsTo(DatabaseServer::class);
    }

    public function volume(): BelongsTo
    {
        return $this->belongsTo(Volume::class);
    }
}
