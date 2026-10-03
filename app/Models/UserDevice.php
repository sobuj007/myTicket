<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class UserDevice extends Model

{
    protected $guarded = ['created_at', 'updated_at'];
    //
    #[Override]
    protected function casts(): array
    {
        return [
            'is_trusted' => 'boolean',
            'is_blocked' => 'boolean',
            'is_active' => 'boolean',
            'first_seent_at' => 'datetime',
            'last_seent_at' => 'datetime',
            'last_login_at' => 'datetime',
            'remove_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
