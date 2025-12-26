<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctorId',
        'cabinetId',
        'availability',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['availabilityData'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctorId');
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinetId');
    }

    public function getAvailabilityDataAttribute()
    {
        if (empty($this->availability)) {
            return null;
        }
        return json_decode($this->availability, true);
    }

    public function setAvailabilityAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['availability'] = json_encode($value);
        } else {
            $this->attributes['availability'] = $value;
        }
    }

    protected $hidden = ['availability'];
}
