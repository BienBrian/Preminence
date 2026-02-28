<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Twilio extends Model
{
    use HasFactory;
    protected $table = 'twilio';
    protected $fillable = ["sid","token","number"];

    public function setSidAttribute($value)
    {
        $this->attributes['sid'] = encrypt($value);
    }

    public function getSidAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = encrypt($value);
    }

    public function getTokenAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
