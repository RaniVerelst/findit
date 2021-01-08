<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    public function company()
    {
        return $this->hasMany('\App\Models\Company');
    }

    public function applications(){
        return $this->hasMany('\App\Models\Application');
    }
}
