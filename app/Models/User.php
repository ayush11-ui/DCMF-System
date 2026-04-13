<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'bar_number',
        'court_id',
        'specialization',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function assignedJudgeCases()
    {
        return $this->hasMany(CaseFile::class, 'assigned_judge_id');
    }

    public function assignedLawyerCases()
    {
        return $this->hasMany(CaseFile::class, 'assigned_lawyer_id');
    }

    public function createdCases()
    {
        return $this->hasMany(CaseFile::class, 'created_by');
    }

    public function scheduleHearings()
    {
        return $this->hasMany(Hearing::class, 'scheduled_by');
    }

    public function notifications()
    {
        return $this->hasMany(DcfmNotification::class);
    }
}
