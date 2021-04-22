<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Filesystem\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class User extends Authenticatable
{
    protected $guarded = [];
    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */

    protected $messages = [
    'img.required' => 'El campo de imagen es obligatorio.',
    'img.image' => 'El campo de imagen debe ser un archivo de imagen valido.',
    'password.required' => 'El campo de contraseña es obligatorio.',
    'email.required' => 'El campo email es obligatorio.',
    'email.unique' => 'El email ingresado ya esta registrado.',
    'name.required' => 'El campo de nombre es obligatorio.',
    'phone.required' => 'El campo de telefono es obligatorio.',
    ];

    public function rules($type)
    {
        if($type === "add")
        {
            return [

            'name'      => 'required|unique:users',
            'phone'     => 'required',
            'email'     => 'required|unique:users',
            'password'  => 'required|min:6',
            'img'       => 'required|image'
            ];
        }
        else
        {
            return [

            'name'      => 'required',
            'phone'     => 'required',
            'email'     => 'required',
            'img'       => 'image'
            ];
        }
    }

    public function validate($data,$type)
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
        $a                          = isset($data['lid']) ? array_combine($data['lid'], $data['l_name']) : [];
        $b                          = isset($data['lid']) ? array_combine($data['lid'], $data['l_address']) : [];
        $add                        = $type === 'add' ? new User : User::find($type);
        $add->name                  = isset($data['name']) ? $data['name'] : null;
        $add->phone                 = isset($data['phone']) ? $data['phone'] : null;
        $add->email                 = isset($data['email']) ? $data['email'] : null;
        $add->status                = isset($data['status']) ? $data['status'] : 0;
        $add->city_id               = isset($data['city_id']) ? $data['city_id'] : 0;
        $add->address               = isset($data['address']) ? $data['address'] : 0;
        $add->delivery_time         = isset($data['delivery_time']) ? $data['delivery_time'] : null;
        $add->person_cost           = isset($data['person_cost']) ? $data['person_cost'] : null;
        $add->lat                   = isset($data['lat']) ? $data['lat'] : null;
        $add->lng                   = isset($data['lng']) ? $data['lng'] : null;
        $add->type                  = isset($data['store_type']) ? $data['store_type'] : null;
        $add->min_cart_value        = isset($data['min_cart_value']) ? $data['min_cart_value'] : null;
        $add->delivery_charges_value = isset($data['delivery_charges_value']) ? $data['delivery_charges_value'] : null;
        $add->available_days        = isset($data['days']) ? $data['days'] : null;
        $add->category_id           = isset($data['store_type']) ? $data['store_type'] : null;

        if(isset($data['admin']))
        {
            $add->c_type                = isset($data['c_type']) ? $data['c_type'] : 0;
            $add->c_value               = isset($data['c_value']) ? $data['c_value'] : 0;
        }

        $add->s_data                = serialize([$a,$b]);

        if(isset($data['img']))
        {
            $filename   = time().rand(111,699).'.' .$data['img']->getClientOriginalExtension();
            $data['img']->move("upload/user/", $filename);
            $add->img = $filename;
        }

        if(isset($data['password']))
        {
            $add->password      = bcrypt($data['password']);
            $add->shw_password  = $data['password'];
        }

        if(isset($data['opening_time']))
        {
            $add->opening_time     = $data['opening_time'];
        }

        if(isset($data['closing_time']))
        {
            $add->closing_time   = $data['closing_time'];
        }

        $add->save();

        $gallery = new UserImage;
        $gallery->addNew($data,$add->id);
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll()
    {
        return User::join('city','users.city_id','=','city.id')
            ->leftjoin('user_category', 'users.category_id', '=', 'user_category.id')
            ->select('city.name as city','users.*', 'user_category.id as cate_id', 'user_category.name as cate_name')
            ->orderBy('users.id','DESC')->get();
    }


    public function getAppData($city_id,$trending = false)
    {
        date_default_timezone_set('America/Monterrey');
        $admin   = Admin::where('id', 1)->select('currency', 'logo')->first();
        $time  = date('H:i:s');

        $res = User::leftjoin('user_category', 'users.category_id', '=', 'user_category.id')
            ->where('users.open', 0)->where('users.status',0)->where('users.city_id',$city_id);


        /** Listado de trendings*/
        if (isset($trending) && $trending === true)
        {
            $res = User::where('trending', 1)->leftjoin('user_category', 'users.category_id', '=', 'user_category.id')
                ->leftJoin('rate', 'users.id', '=', 'rate.user_id')
                ->select('users.id','users.name as title', 'users.img', 'address', 'open', 'trending', 'phone', 'rate.star as rating'
                    , 'opening_time as open_time', 'closing_time as close_time', 'person_cost', 'delivery_time', 'user_category.name as type')
                ->get();

            $res = array_map(function ($value){
                $value['img'] = Asset("upload/user/{$value['img']}");
                return $value;
            }, $res->toArray());
            
            return $res;
        }

        /** Busqueda de items */
        if (isset($_GET['q']))
        {
            $q   = $_GET['q'];
            $items = $res->with(["items" => function($query) use ($q){
                $query->whereRaw('lower(item.name) like "%' . strtolower($q) . '%"');
            }])->select('users.*')->get();

            return $items;
        }

        /** Response general con o sin ubicacion en tiempo real */
        if (isset($_GET['lat']) && isset($_GET['lng']))
        {
            $lat = $_GET['lat'];
            $lng = $_GET['lng'];

            $res = User::join('user_category', 'users.category_id', '=', 'user_category.id')
                ->where('users.open', 0)->where('users.status',0)->where('users.city_id',$city_id);

            $res  = $res->select('users.*', 'user_category.name as cate_name', 'user_category.id as cate_id', 'user_category.img as cate_img',
                DB::raw("ROUND(6371 * acos(cos(radians(" . $lat . "))
                * cos(radians(users.lat))
                * cos(radians(users.lng) - radians(" . $lng . "))
                + sin(radians(" .$lat. "))
                * sin(radians(users.lat)))) AS distance"))
                ->orderBy('distance','ASC')
                ->havingRaw('distance <= ?', [10])
                ->get();

        }
        else {
            $res = $res->select('users.*', 'user_category.name as cate_name', 'user_category.id as cate_id', 'user_category.img as cate_img')
                ->orderBy('open','ASC')->get();
        }

        $data = [];
        foreach($res as $row)
        {
            if ($row['opening_time'] === '00' || $row['closing_time'] === '00'){
                $open = true;
            } else {
                $open = $this->verifyTimes($row);
            }

            $rate = Rate::where('store_id',$row->id)->selectRaw("count(id) as count, sum(star) as sum")->first();
            $totalRate    = $rate->count;
            $totalRateSum = $rate->sum;
            $avg = $totalRate > 0 ? ($totalRateSum / $totalRate) : 0;


            if (isset($row->category_id) && $row->category_id != 0)
                $category = StoreCategory::where('id', $row->category_id)->first('name');


            $data[] = [

                'id'            => $row->id,
                'title'         => $this->getLang($row->id,0)['name'],
                'img'           => Asset("upload/user/{$row->img}"),
                'address'       => $this->getLang($row->id,0)['address'],
                'lat'           => $row->lat,
                'lng'           => $row->lng,
                'open'          => $open,
                'trending'      => $row->trending,
                'logo'          => $admin['logo'] ? Asset("upload/admin/{$admin['logo']}") : null,
                'phone'         => $row->phone,
                'rating'        => $avg > 0 ? number_format($avg, 1) : '0.0',
                'open_time'     => date('h:i A', strtotime($row->opening_time)),
                'close_time'    => date('h:i A',strtotime($row->closing_time)),
                'images'        => $this->userImages($row->id),
                'ratings'       => $this->getRating($row->id),
                'person_cost'   => $row->person_cost,
                'delivery_time' => $row->delivery_time,
                'type'          => isset($category) ? $category['name'] : 'No asignado',
                'currency'      => $admin['currency'],
                'discount'      => $this->discount($row->id,$admin['currency'])['msg'],
                'discount_value'=> $this->discount($row->id,$admin['currency'])['value'],
                'items'         => $this->menuItem($row->id),
                'cate_id'       => $row->cate_id,
                'cate_name'     => $row->cate_name,
                'cate_img'      => $row->cate_img == "" ? "" : Asset("upload/user/category/{$row->cate_img}"),
                'hora'          => $time,
                'distance'      => $row->distance,
            ];

        }

        return $data;
    }

    public function getLang($id,$lid)
    {
        $lid  = $lid > 0 ? $lid : 0;
        $data = User::find($id);

        if($lid == 0)
        {
            return ['name' => $data->name,'address' => $data->address];
        }
        else
        {
            $data = unserialize($data->s_data);

           return ['name' => $data[0][$lid],'address' => $data[1][$lid]];
        }
    }

    public function discount($id,$currency)
    {
        $res =  OfferStore::join('offer','offer_store.offer_id','=','offer.id')
                         ->select('offer.*')
                         ->where('offer.status',0)
                         ->where('offer_store.store_id',$id)
                         ->orderBy('offer.id','DESC')
                         ->first();
        $msg = null;
        $val = 0;

        if(isset($res->id))
        {
            $val = $res->value;

            if($res->type == 0)
            {
                $msg = $res->value."% de descuento con este cupon ".$res->code;
            }
            else
            {
                $msg = $currency.$res->value." de descuento con este cupon ".$res->code;
            }
        }

        return ['msg' => $msg,'value' => $val];
    }

    public function getRating($id)
    {
        $res =  Rate::join('app_user','rate.user_id','=','app_user.id')
                   ->select('app_user.name as user','rate.*')
                   ->where('rate.store_id',$id)
                   ->orderBy('rate.id','DESC')
                   ->get();
        $data = [];

        foreach($res as $row)
        {
            $data[] = ['user' => $row->user,'star' => $row->star,'comment' => $row->comment,'date' => date('d-M-Y',strtotime($row->created_at))];
        }

        return $data;
    }

    public function userImages($id)
    {
        $res = UserImage::where('user_id',$id)->get();
        $data = [];

        foreach($res as $row)
        {
            $data[] = ['img' => Asset('upload/user/gallery/'.$row->img)];
        }

        return $data;
    }

    public function menuItem($id)
    {
        $data     = [];
        $cates    = Item::where('status',0)->where('store_id',$id)->select('category_id')->distinct()->get();

        foreach($cates as $cate)
        {
            $items = Item::where('status',0)->where('category_id',$cate->category_id)->where('store_id',$id)->orderBy('sort_no','ASC')->get();
            $count = [];
            $showOptions = true;

            foreach($items as $i)
            {
                $entra = 0;

                if ($i->large_price != null) {
                    $entra++;
                }

                if ($i->small_price != null) {
                    $entra++;
                }

                if ($i->medium_price != null) {
                    $entra++;
                }

                if ($entra == 1) {
                    $showOptions = false;
                }

                if($i->small_price)
                {
                    $price = $i->small_price;
                }
                elseif(!$i->small_price && $i->medium_price)
                {
                    $price = $i->medium_price;
                }
                elseif(!$i->small_price && !$i->medium_price)
                {
                    $price = $i->large_price;
                }

                if($i->small_price)
                {
                    $count[] = $i->small_price;
                }

                if($i->medium_price)
                {
                    $count[] = $i->medium_price;
                }

                if($i->large_price)
                {
                    $count[] = $i->large_price;
                }

                $lid    = isset($_GET['lid']) && $_GET['lid'] > 0 ? $_GET['lid'] : 0;
                $category   = Category::find($i->category_id);

                $item[] = [

                    'id'            => $i->id,
                    //'name'          => $this->getLangItem($i->id,$_GET['lid'])['name'],
                    'name'          => $this->getLangItem($i->id,$lid)['name'],
                    'img'           => isset($i->img) ? $i->img : null,
                    //'description' => $this->getLangItem($i->id,$_GET['lid'])['desc'],
                    'description'   => $this->getLangItem($i->id,$lid)['desc'],
                    's_price'       => $i->small_price,
                    'm_price'       => $i->medium_price,
                    'l_price'       => $i->large_price,
                    'price'         => $price,
                    'count'         => count($count),
                    'nonveg'        => $i->nonveg,
                    'addon'         => $this->addon($i->id),
                    'status'        => $i->status,
                    'showOptions'   => $showOptions,
                    'cate_id'       => $i->category_id,
                    'cate_name'     => isset($category) ? $category->name : "Sin asignar",
                    'cate_img'      => !isset($category->img) || $category->img == "" ? "" : Asset('upload/item/'.$category->img),
                ];


            }

            //$data[] = ['id' => $cate->category_id,'cate_name' => $this->getLangCate($cate->category_id,$_GET['lid'])['name'],'items' => $item];

            $data[] = [
                'id' => $cate->category_id,
             //   'cate_name' => $this->getLangCate($cate->category_id,$lid)['name'],
                'items' => $item
            ];

            unset($item);

        }

        //print_r( $data);

        return $data;
    }

    public function getLangCate($id,$lid)
    {
        $lid  = $lid > 0 ? $lid : 0;
        $data = Category::find($id);

        if($lid == 0)
        {
            return ['name' => $data->name];
        }
        else
        {
            $data = unserialize($data->s_data);

            return ['name' => $data[$lid]];
        }
    }

    public function getLangItem($id,$lid)
    {
        $lid  = $lid > 0 ? $lid : 0;
        $data = Item::find($id);

        if($lid == 0)
        {
            return ['name' => $data->name,'desc' => $data->description];
        }
        else
        {
            $data = unserialize($data->s_data);

            return ['name' => $data[0][$lid],'desc' => $data[1][$lid]];

        }
    }

    public function addon($id)
    {
       return ItemAddon::join('addon','item_addon.addon_id','=','addon.id')
                        ->select('addon.*')
                        ->where('item_addon.item_id',$id)
                        ->get();
    }

    public function overview()
    {
        return [

        'order'     => Order::where('store_id',Auth::user()->id)->count(),
        'complete'  => Order::where('store_id',Auth::user()->id)->where('status',4)->count(),
        'month'     => Order::where('store_id',Auth::user()->id)->whereDate('created_at','LIKE',date('Y-m').'%')
                            ->count(),
        'items'     => Item::where('store_id',Auth::user()->id)->where('status',0)->count()

        ];
    }

    public function getSData($data,$id,$field)
    {
        $data = unserialize($data);

        return isset($data[$field][$id]) ? $data[$field][$id] : null;
    }

   public function login($data)
   {
     $chk = User::where('status',0)->where('email',$data['email'])->where('shw_password',$data['password'])->first();

     if(isset($chk->id))
     {
        return ['msg' => 'done','user_id' => $chk->id];
     }
     else
     {
        return ['msg' => 'Opps! Datos de acceso incorrectos'];
     }
   }

   public function getCom($id,$total)
   {
     $order = Order::find($id);
     $user  = User::find($order->store_id);

     if($user->c_type == 0)
     {
        $val = $user->c_value;
     }
     else
     {
        $val = round($total * intval(str_replace('%', '', $user->c_value)) / 100);
     }

     return $val;
   }

    public function items()
    {
        return $this->hasMany(Item::class, "store_id", "id");
    }


    /** Helpers */
    public function verifyTimes($store){
        try {

            $now = Carbon::now();
            $op =  Carbon::parse($store['opening_time']);
            $cl =  Carbon::parse($store['closing_time']);

            if ($cl->lt($op) && $op->lt($now))
                return true;

            if ($now > $op && $now < $cl)
                return true;

            return false;

        } catch (\Exception $e){
            return ['success' => false,'message' => $e->getMessage()];
        }
    }


    /** Relationships */
    public function category(){

        return $this->belongsTo(StoreCategory::class, 'category_id');
    }
}
