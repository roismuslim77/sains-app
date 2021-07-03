<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{

    public $timestamps = false;
    protected $table = 'classes';
    protected $guarded = [];
}
