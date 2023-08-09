<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    public $table = 'likes';//nome esatto della tabella
    public $timestamps = false;//non voglio le colonne updated_at o robe del genere
    use HasFactory;
}
