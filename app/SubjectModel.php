<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubjectModel extends Model
{

    public $timestamps = false;
    protected $table = 'subjects';
    protected $guarded = [];
}
