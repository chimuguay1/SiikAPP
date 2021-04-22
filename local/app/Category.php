<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Validator;
class Category extends Authenticatable
{
    protected $table = "category";
    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */

    protected $messages = [
        'img.required' => 'El campo de imagen es obligatorio.',
        'img.image' => 'El campo de imagen debe ser un archivo de imagen valido.',
        'name.required' => 'El campo de nombre es obligatorio.',
        'name.string' => 'El valor del campo de nombre no es valido.',
        'status.required' => 'El campo de estado es obligatorio.',
        'status.boolean' => 'El valor del campo de estado debe ser habilitado o deshabilitado.',
        'sort_no.required' => 'El campo de orden de clasificacion es obligatorio.',
        'sort_no.numeric' => 'El valor del campo de orden de clasificacion debe ser numerico.',
    ];

    public function rules($type)
    {
        if($type === "add")
        {
            return [

                'name'      => 'required|string',
                'status'    => 'required|boolean',
                'img'       => 'image',
                'sort_no'   => 'required|numeric',
            ];
        }
        else
        {
            return [

                'name'      => 'string',
                'status'     => 'boolean',
                'img'       => 'image',
                'sort_no'   => 'numeric',
            ];
        }
    }

    public function validate($data,$type = "")
    {
        $validator = Validator::make($data,$this->rules($type), $this->messages);
        if($validator->fails())
        {
            return $validator;
        }
    }

    /*
    |--------------------------------
    |Create/Update user
    |--------------------------------
    */

    public function addNew($data,$type)
    {
        $a                  = isset($data['lid']) ? array_combine($data['lid'], $data['l_name']) : [];
        $add                = $type === 'add' ? new Category : Category::find($type);
        $add->store_id      = isset(Auth::user()->id) ? Auth::user()->id : 1;
        $add->name          = isset($data['name']) ? $data['name'] : null;
        $add->status        = isset($data['status']) ? $data['status'] : null;
        $add->sort_no       = isset($data['sort_no']) ? $data['sort_no'] : 0;
        $add->s_data        = serialize($a);


        if (!isset($data['img'])) {

            $old_img = $add->img;
            if (isset($old_img) && $old_img != ""){
                $old_img = "upload/item/".$old_img;

                if(file_exists($old_img))
                    unlink($old_img);
            }

            $add->img = "";
        }

        if(isset($data['img']) && $data['img'] !== "")
        {
            $old_img = $add->img;

            $filename = time() . rand(111, 699) . '.' . $data['img']->getClientOriginalExtension();
            $data['img']->move("upload/item", $filename);
            $add->img = $filename;


            if (isset($old_img) && $old_img != ""){
                $old_img = "upload/item/".$old_img;

                if(file_exists($old_img))
                    unlink($old_img);
            }
        }

        $add->save();
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll()
    {
        return Category::where('store_id',Auth::user()->id)->orderBy('name', 'ASC')->orderBy('sort_no','ASC')->get();
    }

    public function getAllPaginated($quantity = 10){
        return Category::where('store_id',Auth::user()->id)->orderBy('name', 'ASC')->orderBy('sort_no','ASC')->paginate($quantity);
    }

    public function getSData($data,$id,$field)
    {
        $data = unserialize($data);

        return isset($data[$id]) ? $data[$id] : null;
    }
}
