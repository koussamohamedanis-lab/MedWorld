<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
        'phoneNumber',
        'avatarUrl',
        'address',
        'gender',
        'dateOfBirth',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dateOfBirth' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['fullName'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    public function getFullNameAttribute()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'userId');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'userId');
    }

    public function assistant()
    {
        return $this->hasOne(Assistant::class, 'userId');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'senderId');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiverId');
    }

    public function messages()
    {
        return Message::where('senderId', $this->id)
            ->orWhere('receiverId', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
