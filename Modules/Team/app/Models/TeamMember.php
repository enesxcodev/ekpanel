<?php

namespace Modules\Team\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Team\Database\Factories\TeamMemberFactory;

class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    protected $fillable = ['name', 'title', 'image', 'is_active', 'extra_fields'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    protected static function newFactory(): TeamMemberFactory
    {
        return TeamMemberFactory::new();
    }
}
