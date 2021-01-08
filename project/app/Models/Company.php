<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public function internships(){
        return $this->hasMany('\App\Models\Internship');
    }

    public function applications(){
        return $this->hasManyThrough('App\Models\Application', 'App\Models\Internship');
    }
}
