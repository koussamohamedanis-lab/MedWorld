<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'speciality',
        'licenseNumber',
        'careerStart',
        'consultationPrice',
        'consultationDuration',
        'cabinetId',
    ];

    protected $casts = [
        'careerStart' => 'datetime',
        'consultationPrice' => 'float',
        'consultationDuration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinetId');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class, 'doctorId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctorId');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'doctorId');
    }

    public function assistants()
    {
        return $this->hasMany(Assistant::class, 'doctorId');
    }

    public function getYearsOfExperienceAttribute()
    {
        if (!$this->careerStart) {
            return 0;
        }
        return now()->diffInYears($this->careerStart);
    }
}
