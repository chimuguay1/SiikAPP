<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Auth;
class Rate extends Authenticatable
{
   protected $table = 'rate';

   public function addNew($data)
   {
       try {
           $order            = Order::find($data['oid']);

           if (!isset($order))
               return response()->json(['success' => false,'message' => "No se ha encontrado la orden seleccionada"]);

           $add              = new Rate;
           $add->user_id     = $data['user_id'];
           $add->store_id    = $order->store_id;
           $add->order_id    = isset($data['oid']) ? $data['oid'] : 0;
           $add->star        = $data['star'];
           $add->comment     = isset($data['comment']) ? $data['comment'] : null;
           $add->save();

           return ['data' => true];

       } catch (\Exception $e){
           return response()->json(['success' => false,'message' => $e->getMessage()]);
       }
   }
}
