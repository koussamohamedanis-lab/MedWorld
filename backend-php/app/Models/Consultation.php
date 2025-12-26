<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctorId',
        'patientId',
        'appointmentId',
        'notes',
        'prescriptions',
        'attachments',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctorId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointmentId');
    }
}
