<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    public $table = 'comments';//nome esatto della tabella
    public $timestamps = false;//non voglio le colonne updated_at o robe del genere
    public $dateFormat = 'd-m-Y';
    use HasFactory;
}
