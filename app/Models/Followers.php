<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    public $table = 'followers';//nome esatto della tabella
    public $timestamps = false;//non voglio le colonne updated_at o robe del genere
    use HasFactory;
}
