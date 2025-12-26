<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    const STATUS_SCHEDULED = 'SCHEDULED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_NO_SHOW = 'NO_SHOW';

    protected $fillable = [
        'date',
        'status',
        'patientId',
        'doctorId',
        'cabinetId',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctorId');
    }

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinetId');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class, 'appointmentId');
    }
}
