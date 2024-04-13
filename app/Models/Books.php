<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books
{
    use HasFactory;

    protected $fillable = ['name', 'id', 'desc'];

}
