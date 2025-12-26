<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assistant extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'doctorId',
        'cabinetId',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctorId');
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinetId');
    }
}
