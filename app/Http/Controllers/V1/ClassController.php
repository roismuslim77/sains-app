<?php

namespace App\Http\Controllers\V1;

use App\ClassModel;
use App\Helpers\Format;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassController extends Controller
{    
    public function listclass()
    {
        $classes = ClassModel::select('*')
            ->whereRaw('deleted_datetime is null')
            ->get();

        return Format::response($classes);
    }
}
