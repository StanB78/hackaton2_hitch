<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROL_REIZIGER = 'reiziger';
    public const ROL_CHAUFFEUR = 'chauffeur';

    // 'rol' staat bewust NIET in $fillable: een gebruiker mag zijn eigen rol nooit via een formulier instellen.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // wachtwoorden worden gehasht opgeslagen
        ];
    }

    public function isChauffeur(): bool
    {
        return $this->rol === self::ROL_CHAUFFEUR;
    }

    public function isReiziger(): bool
    {
        return $this->rol === self::ROL_REIZIGER;
    }

    public function ritten(): HasMany
    {
        return $this->hasMany(Rit::class);
    }

    public function chauffeursritten(): HasMany
    {
        return $this->hasMany(Rit::class, 'chauffeur_id');
    }
}
