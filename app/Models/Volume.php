<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $type
 * @property array<array-key, mixed> $config
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Backup> $backups
 * @property-read int|null $backups_count
 *
 * @method static \Database\Factories\VolumeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Volume whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Volume extends Model
{
    /** @use HasFactory<\Database\Factories\VolumeFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'name',
        'type',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }
}
