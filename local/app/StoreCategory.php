<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;

class StoreCategory extends Model
{
    protected $table = 'user_category';

    protected $fillable = ['name', 'status'];

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
    ];

    public function rules($type)
    {
        if($type === "add")
        {
            return [

                'name'      => 'required|string',
                'status'     => 'required|boolean',
                'img'       => 'image'
            ];
        }
        else
        {
            return [

                'name'      => 'string',
                'status'     => 'boolean',
                'img'       => 'image'
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


    /** Relationships */
    public function users(){

        return $this->hasMany(User::class);
    }
}
