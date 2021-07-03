<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubjectMatterModel extends Model
{

    public $timestamps = false;
    protected $table = 'subject_matters';
    protected $guarded = [];
}
