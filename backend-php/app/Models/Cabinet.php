<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabinet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'image',
        'adminId',
        'location',
        'openingHours',
        'accessHandicap',
        'hasParking',
        'hasWifi',
        'acceptsUrgent',
        'acceptsInsurance',
    ];

    protected $casts = [
        'accessHandicap' => 'boolean',
        'hasParking' => 'boolean',
        'hasWifi' => 'boolean',
        'acceptsUrgent' => 'boolean',
        'acceptsInsurance' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'adminId');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'cabinetId');
    }

    public function assistants()
    {
        return $this->hasMany(Assistant::class, 'cabinetId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'cabinetId');
    }
}
