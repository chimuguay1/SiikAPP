<?php

namespace App\Http\Controllers\Admin;

use App\StoreCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StoreCategoryController extends Controller
{

    public function index()
    {
        $cat = StoreCategory::paginate(10);

        return View('admin.storeCategory.index',['data' => $cat,'link' => env('admin').'/storeCategory/']);
    }


    public function create()
    {
        return View('admin.storeCategory.add',['data' => new StoreCategory,'form_url' => env('admin').'/storeCategory']);

    }


    public function store(Request $request)
    {
        $storeCategory = new StoreCategory;

        if($storeCategory->validate($request->all(),'add'))
        {
            return redirect()->back()->withErrors($storeCategory->validate($request->all(),'add'))->withInput();
            exit;
        }

        $storeCategory->name = $request->name;
        $storeCategory->status = $request->status;

        if($request->img)
        {
            $filename   = time().rand(111,699).'.' .$request->img->getClientOriginalExtension();
            $request->img->move("upload/user/category", $filename);
            $storeCategory->img = $filename;
        }

        $storeCategory->save();

        return redirect()->to('admin/storeCategory')->with('message','Nuevo registro agregado con éxito.');
    }


    public function show($id)
    {
        //
    }


    public function edit(StoreCategory $storeCategory)
    {
        return View('admin.storeCategory.edit',['data' => $storeCategory,'form_url' => env('admin').'/storeCategory/'.$storeCategory->id]);
    }


    public function update(Request $request, StoreCategory $storeCategory)
    {
        try {
            if($storeCategory->validate($request->all()))
            {
                return redirect()->back()->withErrors($storeCategory->validate($request->all()))->withInput();
                exit;
            }

            $storeCategory->name = $request->name;
            $storeCategory->status = $request->status;
            $old_img = null;

            if (!isset($request->img)){
                $old_img = $storeCategory->img;

                if (isset($old_img) && $old_img != ""){
                    $old_img = "upload/user/category/".$old_img;

                    if(file_exists($old_img))
                        unlink($old_img);
                }

                $storeCategory->img = "";
            }

            if(isset($request->img))
            {
                $old_img = $storeCategory->img !== "" ? "upload/user/category/".$storeCategory->img : "";

                $filename   = time().rand(111,699).'.' .$request->img->getClientOriginalExtension();
                $request->img->move("upload/user/category", $filename);
                $storeCategory->img = $filename;

                if($storeCategory->img !== "" && file_exists($old_img))
                    unlink($old_img);

            }

            $storeCategory->save();

            return redirect()->to('admin/storeCategory')->with('message','Se ha actualizado el registro con éxito.');

        } catch (\Exception $e){
            return redirect()->back()->with('error', 'Ha ocurrido un error al actualizar la categoria.');
        }
    }

    public function delete(StoreCategory $id)
    {
        $id->delete();

        return redirect()->to('admin/storeCategory')->with('message','Se ha eliminado el registro con éxito.');
    }

    public function status(StoreCategory $id)
    {
        $id->status = !$id->status;
        $id->save();

        return redirect()->to('admin/storeCategory')->with('message','Se ha actualizado el estado con éxito.');
    }
}
