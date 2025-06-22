<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'dropbox_id',
        'status',
    ];

    /**
     * Mendefinisikan relasi: Satu History dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendefinisikan relasi: Satu History terjadi di satu Dropbox.
     */
    public function dropbox()
    {
        return $this->belongsTo(Dropbox::class);
    }
}
