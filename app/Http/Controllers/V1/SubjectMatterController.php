<?php

namespace App\Http\Controllers\V1;

use App\ClassModel;
use App\Helpers\Format;
use App\Http\Controllers\Controller;
use App\SubjectMatterModel;
use App\SubjectModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubjectMatterController extends Controller
{    
    public function __construct()
    {
        $this->uploadDir = 'assets/subject_matters';
    }

    public function listmatter()
    {
        $matters = SubjectMatterModel::select('subject_matters.*', 'subjects.name as subject_name', 'classes.name as class_name')
            ->join('subjects', 'subjects.id', '=', 'subject_matters.subject_id')
            ->join('classes', 'subjects.class_id', '=', 'classes.id')
            ->whereRaw('subject_matters.deleted_user_id is null')
            ->whereRaw('subject_matters.status = 1')
            ->get();

        // return $matters;
        return Format::response($matters);
    }

    public function showmatter($id)
    {
        $matter = SubjectMatterModel::select('subject_matters.*', 'subjects.name as subject_name', 'classes.name as class_name')
            ->join('subjects', 'subjects.id', '=', 'subject_matters.subject_id')
            ->join('classes', 'subjects.class_id', '=', 'classes.id')
            ->whereRaw('subject_matters.deleted_user_id is null')
            ->whereRaw('subject_matters.status = 1')
            ->where('subject_matters.id', $id)
            ->first();

        if(!$matter) abort(200, 'Data tidak ditemukan.');

        return Format::response($matter);
    }

    public function addmatter(Request $request)
    {
        $validatedData = $this->validate($request, [
            'subject_id' => 'required',
            'name' => 'required',
            'type_id' => 'required',
            'notes' => 'required',
            'contents' => 'nullable',
            'sub_contents' => 'nullable',
            'file' => 'nullable',
            'user_id' => 'required'
        ]);

        //validasi params content n file
        if($validatedData['type_id'] == 1){
            if(!$request->has('contents') || $request->contents == '') abort(200, 'Parameter Contents wajib diisi.');
        }

        DB::beginTransaction();
        try {
            $store_data = [
                'subject_id' => $validatedData['subject_id'],
                'type_id' => $validatedData['type_id'],
                'name' => $validatedData['name'],
                'notes' => $validatedData['notes'],
                'contents' => $validatedData['contents'] ?? null,
                'sub_contents' => $validatedData['sub_contents'] ?? null,
                'file_name' => $validatedData['file_name'] ?? null,
                'status' => 1,
                'created_datetime' => Carbon::now('+07:00'),
                'created_user_id' => $validatedData['user_id'],
                'updated_datetime' => Carbon::now('+07:00'),
                'updated_user_id' => $validatedData['user_id']
            ];

            $matter_id = SubjectMatterModel::insertGetId($store_data);
            
            if($validatedData['type_id'] == 2){
                //upload file pdf in public
                if ($request->hasFile('file') && $request->file('file')->isValid()) {
                    $filename = $validatedData['subject_id'] .'_'. str_replace(' ', '_', strtolower($validatedData['name']));
                    $filename .= '.' . $request->file('file')->guessClientExtension();
                    $request->file('file')->move($this->uploadDir, $filename);
                    $store_data['file_name'] = $filename;

                    //update file name
                    SubjectMatterModel::where('id', $matter_id)
                        ->update([
                            'file_name' => $store_data['file_name'],
                            'updated_datetime' => Carbon::now('+07:00'),
                            'updated_user_id' => $validatedData['user_id']
                        ]);
                }
            }

            $store_data['id'] = $matter_id;
            DB::commit();
            return Format::response($store_data);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(200, $th->getMessage());
        }
    }

    public function editmatter(Request $request, $id)
    {
        $validatedData = $this->validate($request, [
            'subject_id' => 'required',
            'name' => 'required',
            'type_id' => 'required',
            'notes' => 'required',
            'status' => 'required',
            'contents' => 'required',
            'sub_contents' => 'required',
            'file' => 'nullable',
            'user_id' => 'required'
        ]);

        //validasi params content n file
        if($validatedData['type_id'] == 1){
            if(!$request->has('contents') || $request->contents == '') abort(200, 'Parameter Contents wajib diisi.');
        }

        DB::beginTransaction();
        try {

            $matter = SubjectMatterModel::where('id', $id)
                ->first();

            $matter->subject_id = $validatedData['subject_id'];
            $matter->type_id = $validatedData['type_id'];
            $matter->name = $validatedData['name'];
            $matter->notes = $validatedData['notes'];
            $matter->contents = $validatedData['contents'];
            $matter->sub_contents = $validatedData['sub_contents'];
            $matter->status = $validatedData['status'];
            $matter->updated_datetime = Carbon::now('+07:00');
            $matter->updated_user_id = $validatedData['user_id'];
            $matter->update();
            
            if($validatedData['type_id'] == 2){
                //upload file pdf in public
                if ($request->hasFile('file') && $request->file('file')->isValid()) {
                    $filename = $validatedData['subject_id'] .'_'. str_replace(' ', '_', strtolower($validatedData['name'])).'_'.$request->file('file')->getClientOriginalName();
                    $newPicture = $request->file('file')->move($this->uploadDir, $filename);
                    if ($newPicture) {
        
                        $oldPicture = $this->uploadDir . '/' . $matter->file_name;
        
                        if (file_exists($oldPicture) && $matter->file_name) {
                            unlink($oldPicture);
                        }
        
                        $matter['file_name'] = $filename;

                        //update file name
                        SubjectMatterModel::where('id', $id)
                            ->update([
                                'file_name' => $matter['file_name'],
                                'updated_datetime' => Carbon::now('+07:00'),
                                'updated_user_id' => $validatedData['user_id']
                            ]);
                    }
                }
            }

            DB::commit();
            return Format::response($matter);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(200, $th->getMessage());
        }
    }
}
