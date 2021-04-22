<?php namespace App\Http\Controllers\api;

use App\Category;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Item;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Auth;
use App\City;
use App\OfferStore;
use App\Offer;
use App\User;
use App\Cart;
use App\CartCoupen;
use App\AppUser;
use App\Order;
use App\Lang;
use App\Rate;
use App\Slider;
use App\Banner;
use App\Address;
use App\Admin;
use App\Page;
use App\Language;
use App\Text;
use App\Delivery;
use Complex\Exception;
use DB;
use Illuminate\Support\Facades\Log;
use Validator;
use Redirect;
use Excel;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Stripe;

class ApiController extends Controller {

    public function configuration()
	{
        try {
            return response()->json(['data' => Admin::find(1)]);

        } catch (\Exception $e){
            Log::error('ApiController::configuration ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar el perfil']);
        }
	}

	public function welcome()
	{
        try {
            $res = new Slider;

            return response()->json(['data' => $res->getAppData()]);

        } catch (\Exception $e){
            Log::error('ApiController::welcome ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function city()
	{
        try {
            date_default_timezone_set('America/Monterrey');
            $city = new City;
            $text = new Text;
            $lid =  isset($_GET['lid']) && $_GET['lid'] > 0 ? $_GET['lid'] : 0;

            if ($id = \request('id')){
                $available_rest = User::where('city_id', $id)->where('open', 0)->where('status', 0)->get()->toArray();
                $open_stores = [];

                foreach ($available_rest as $store){

                    if ($store['opening_time'] == '00' || $store['closing_time'] == '00'){
                        array_push($open_stores, $store);
                    } else {
                        $result = $this->verifyTimes($store);

                        !isset($result) ?: array_push($open_stores, $result);
                    }
                }

                return response()->json(['data' => $open_stores,'text'		=> $text->getAppData($lid)]);
            }

            return response()->json(['data' => $city->getAll(0),'text'		=> $text->getAppData($lid)]);

        } catch (\Exception $e){
            Log::error('ApiController::city ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function lang()
	{
        try {
            $res = new Language;

            return response()->json(['data' => $res->getWithEng()]);

        } catch (\Exception $e){
            Log::error('ApiController::lang ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function homepage($city_id)
	{
        try {
            $banner  = new Banner;
            $store   = new User;
            $text    = new Text;
            $lid     =  isset($_GET['lid']) && $_GET['lid'] > 0 ? $_GET['lid'] : 0;
            $l 		 = Language::find($lid);
            // $stores = \DB::table('user_category')->select('id','name','img')->get();

            // foreach ($stores as $item){
            //     $item->img = $item->img == "" ? "" : Asset('upload/user/category/'.$item->img);
            // }

            // $data = [

            //     'banner'	=> $banner->getAppData($city_id,0),
            //     'middle'	=> $banner->getAppData($city_id,1),
            //     'bottom'	=> $banner->getAppData($city_id,2),
            //     'store_categories' => $stores,
            //     'store' 	=> $store->getAppData($city_id),
            //     'trending'	=> $store->getAppData($city_id,true),
            //     'text'		=> $text->getAppData($lid),
            //     'app_type'	=> isset($l->id) ? $l->type : 0,
            //     'admin'		=> Admin::find(1)

            // ];

            $data = [

                'banner'	=> $banner->getAppData($city_id,0),
                'middle'	=> $banner->getAppData($city_id,1),
                'bottom'	=> $banner->getAppData($city_id,2),
                'store_categories' => [],
                'store' 	=> [],
                'trending'	=> [],
                'text'		=> $text->getAppData($lid),
                'app_type'	=> isset($l->id) ? $l->type : 0,
                'admin'		=> Admin::find(1)

            ];

            return response()->json(['data' => $data]);

        } catch (\Exception $e){
            Log::error('ApiController::homepage ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

    public function getStores($city_id)
	{
        try {
            $store   = new User;
            $stores = \DB::table('user_category')->select('id','name','img')->get();

            foreach ($stores as $item){
                $item->img = $item->img == "" ? "" : Asset('upload/user/category/'.$item->img);
            }

            $data = [
                'store_categories' => $stores,
                'store' 	=> $store->getAppData($city_id),
                'trending'	=> $store->getAppData($city_id,true),

            ];

            return response()->json(['data' => $data]);

        } catch (\Exception $e){
            Log::error('ApiController::getStores ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            // return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
            return response()->json(['success' => false,'message' => 'ApiController::getStores ' . $e->getMessage(), ['error_line' => $e->getLine()]]);
        }
	}

    public function getBanners($city_id)
	{
        try {
            $banner  = new Banner;

            $data = [

                'banner'	=> $banner->getAppData($city_id,0),
                'middle'	=> $banner->getAppData($city_id,1),
                'bottom'	=> $banner->getAppData($city_id,2)
            ];

            return response()->json(['data' => $data]);

        } catch (\Exception $e){
            Log::error('ApiController::getBanners ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function search($query,$type,$city)
	{
        try {
            $user = new User;

            return response()->json(['data' => $user->getUser($query,$type,$city)]);

        } catch (\Exception $e){
            Log::error('ApiController::search ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function addToCart(Request $Request)
	{
        try {
            $res = new Cart;

            return response()->json(['data' => $res->addNew($Request->all())]);

        } catch (\Exception $e){
            Log::error('ApiController::addToCart ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
        }
	}

	public function updateCart($id,$type)
	{
        try {
            $res = new Cart;

            return response()->json(['data' => $res->updateCart($id,$type)]);

        } catch (\Exception $e){
            Log::error('ApiController::updateCart ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al actualizar los datos']);
        }
    }

    public function getCurrentOrder()
	{
        try {
            $order = null;

            if(isset($_GET['user_id']) && $_GET['user_id'] > 0)
            {
                $order = Order::where('user_id',$_GET['user_id'])->whereIn('status',[0,1,3,4,5])->first();

            }

            $order->store = User::find($order->store_id)->name;

            return response()->json([
                'data' => array(
                    "currency" => "$",
                    "data" => $order
                )
            ]);

        } catch (\Exception $e){
            Log::error('ApiController::getCurrentOrder ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function cartCount($cartNo)
	{
        try {
            if(isset($_GET['user_id']) && $_GET['user_id'] > 0)
            {
                $order = Order::where('user_id',$_GET['user_id'])->whereIn('status',[0,1,3,4,5])->count();

            }
            else
            {
                $order = 0;
            }

            $cart = new Cart;

            return response()->json([

                'data'  => Cart::where('cart_no',$cartNo)->count(),
                'order' => $order,
                'cart'	=> $cart->getItemQty($cartNo)

            ]);

        } catch (\Exception $e){
            Log::error('ApiController::cartCount ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function getCart($cartNo)
	{
        try {
            $res = new Cart;

            return response()->json(['data' => $res->getCart($cartNo)]);

        } catch (\Exception $e){
            Log::error('ApiController::getCart ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function getOffer($cartNo)
	{
        try {
            $res = new Offer;

            return response()->json(['data' => $res->getOffer($cartNo)]);

        } catch (\Exception $e){
            Log::error('ApiController::getOffer ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function applyCoupen($id,$cartNo)
	{
        try {
            $res = new CartCoupen;
            $cart = Cart::where('cart_no',$cartNo)->first();
            $user = User::find($cart->store_id);
            $offer = Offer::find($id);

            if (!isset($user))
                return response()->json(['success' => false, 'message' => 'Uno de los productos seleccionados no esta asociado a un negocio valido.']);

            if (!isset($offer))
                return response()->json(['success' => false, 'message' => 'El cupon solicitado no se encuentra en nuestros registros.']);

            return response()->json($res->addNew($id,$cartNo));

        } catch (\Exception $e){
            Log::error('ApiController::applyCoupen ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
        }
	}

	public function signup(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->addNew($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::signup ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            // return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
            return response()->json(['success' => false,'message' => 'ApiController::signup ' . $e->getMessage(), ['error_line' => $e->getLine()]]);
        }
	}

	public function login(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->login($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::login ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar el perfil']);
        }
	}

	public function forgot(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->forgot($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::forgot ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar la peticion']);
        }
	}

	public function verify(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->verify($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::verify ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
	}

	public function updatePassword(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->updatePassword($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::updatePassword ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al actualizar los datos']);
        }
	}

	public function loginFb(Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->loginFb($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::loginFb ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
	}

	public function getAddress($id)
	{
        try {
            $address = new Address;
            $cart 	 = new Cart;

            $data 	 = [

                'address'	 => $address->getAll($id),
                'admin'      => Admin::find(1),
                'total'   	 => $cart->getCart($_GET['cart_no'])['total']

            ];

            return response()->json(['data' => $data]);

        } catch (\Exception $e){
            Log::error('ApiController::getAddress ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function addAddress(Request $Request)
	{
        try {
            $res = new Address;

            return response()->json($res->addNew($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::addAddress ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
        }
    }

    public function removeAddress($id)
	{
        try {
            $res = new Address;

            return response()->json($res->removeNew($id));

        } catch (\Exception $e){
            Log::error('ApiController::removeAddress ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al eliminar los datos']);
        }
	}

	public function order(Request $Request)
	{
        try {
            $res = new Order;

            return response()->json($res->addNew($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::order ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
        }
	}

	public function userinfo($id)
	{
        try {
            return response()->json(['data' => AppUser::find($id)]);

        } catch (\Exception $e){
            Log::error('ApiController::userinfo ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar el perfil']);
        }
	}

	public function updateInfo($id,Request $Request)
	{
        try {
            $res = new AppUser;

            return response()->json($res->updateInfo($Request->all(),$id));

        } catch (\Exception $e){
            Log::error('ApiController::updateInfo ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al actualizar los datos']);
        }
	}

	public function cancelOrder($id,$uid)
	{
        try {
            $res = new Order;

            return response()->json($res->cancelOrder($id,$uid));

        } catch (\Exception $e){
            Log::error('ApiController::cancelOrder ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al actualizar los datos']);
        }
	}

	public function sendChat(Request $Request)
	{
        try {
            $user = new AppUser;

            return response()->json($user->sendChat($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::sendChat ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
	}

	public function rate(Request $Request)
	{
        try {
            $rate = new Rate;

            return response()->json($rate->addNew($Request->all()));

        } catch (\Exception $e){
            Log::error('ApiController::rate ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al registrar los datos']);
        }
	}

	public function pages()
	{
        try {
            $res = new Page;

            return response()->json(['data' => $res->getAppData()]);

        } catch (\Exception $e){
            Log::error('ApiController::pages ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

	public function myOrder($id)
	{
        try {
            $res = new Order;

            return response()->json(['data' => $res->history($id)]);

        } catch (\Exception $e){
            Log::error('ApiController::myOrder ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
    }

    public function conektaCard() {

        try {
            \Conekta\Conekta::setApiVersion("2.0.0");
            \Conekta\Conekta::setLocale('es');
            \Conekta\Conekta::setApiKey(Admin::find(1)->stripe_api_id);

            if(isset($_GET['user_id']) && $_GET['user_id'] > 0) {

                $user = AppUser::find($_GET['user_id']);

                if (isset($user->id_conekta)) {
                    $customer = \Conekta\Customer::find($user->id_conekta);
                    $data = [];
                    foreach($customer->payment_sources as $item) {
                        array_push($data, $item);
                    }

                    return response()->json(['data' => $data]);

                } else {
                    return response()->json(['data' => []]);
                }
            } else {
                return response()->json(['data' => "error"]);
            }

        } catch (\Exception $e){
            Log::error('ApiController::conektaCard ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
    }

	public function stripe()
	{
        try {
            if(isset($_GET['user_id']) && $_GET['user_id'] > 0) {

                $user = AppUser::find($_GET['user_id']);
                $newCard = filter_var($_GET['newCard'], FILTER_VALIDATE_BOOLEAN);

                \Conekta\Conekta::setApiVersion("2.0.0");
                \Conekta\Conekta::setLocale('es');
                \Conekta\Conekta::setApiKey(Admin::find(1)->stripe_api_id);

                if (!isset($user->id_conekta)) {
                    /**Creamos el cliente */
                    try {
                        /*$customer = \Conekta\Customer::create(
                        [
                            "name" => $_GET['name'],
                            "email" => $user->email,
                            "phone" => $user->phone,
                            "payment_sources" => [
                            [
                                "type" => "card",
                                "token_id" => $_GET['token']
                            ]
                            ]
                        ]);*/

                        $customer = \Conekta\Customer::create(
                            [
                                "name" => $_GET['name'],
                                "email" => $user->email,
                                "phone" => $user->phone
                            ]);

                        $user->id_conekta = $customer->id;

                        $source = $customer->createPaymentSource(
                            [
                                "type" => "card",
                                "token_id" => $_GET['token']
                            ]
                        );

                        $res = new AppUser;

                        $res->updateInfo($user,$user->id);

                    } catch (Exception $e) {
                        return response()->json(['data' => "error"]);
                    }
                } else if ($newCard) {
                    $customer = \Conekta\Customer::find($user->id_conekta);
                    $source = $customer->createPaymentSource(
                        [
                            "type" => "card",
                            "token_id" => $_GET['token']
                        ]
                    );
                }

                if (!$newCard) {

                    $valid_order =
                        [
                            'line_items'=> [
                                [
                                    'name'        => 'Comida Rapida',
                                    'description' => 'Cobro de comida rapida.',
                                    'unit_price'  => $_GET['amount'] * 100,
                                    'quantity'    => 1,
                                    'sku'         => 'cohb_s1',
                                    'category'    => 'food',
                                    'tags'        => ['food']
                                ]
                            ],
                            'currency' => 'mxn',
                            "customer_info" => [
                                "customer_id" => $user->id_conekta
                            ],
                            'currency' => 'mxn',
                            'charges' => [
                                [
                                    'payment_method' => [
                                        "type" => "card",
                                        "payment_source_id" => $_GET['token']
                                    ]
                                ]
                            ]
                        ];
                } else {
                    $valid_order =
                        [
                            'line_items'=> [
                                [
                                    'name'        => 'Comida Rapida',
                                    'description' => 'Cobro de comida rapida.',
                                    'unit_price'  => $_GET['amount'] * 100,
                                    'quantity'    => 1,
                                    'sku'         => 'cohb_s1',
                                    'category'    => 'food',
                                    'tags'        => ['food']
                                ]
                            ],
                            'currency' => 'mxn',
                            "customer_info" => [
                                "customer_id" => $user->id_conekta
                            ],
                            'currency' => 'mxn',
                            'charges' => [
                                [
                                    'payment_method' => [
                                        "type" => "card",
                                        "payment_source_id" => $source->id
                                    ]
                                ]
                            ]
                        ];
                }

                try {
                    //$order = \Conekta\Order::create($validOrder);
                    $order = \Conekta\Order::create($valid_order);

                    if($order->payment_status === "paid")
                    {
                        return response()->json(['data' => "done",'id' => $order->id]);
                    }
                    else
                    {
                        return response()->json(['data' => "error"]);
                    }
                } catch (\Conekta\Handler $error) {
                    dd($error);
                    //Normal object methods
                    echo($error->getMessage());
                    echo($error->getCode());
                    echo($error->getLine());
                    echo($error->getTraceAsString());

                    //Conekta object
                    var_dump($error->getConektaMessage());

                    //Conekta object props
                    $conektaError = $error->getConektaMessage();
                    var_dump($conektaError->type);
                    var_dump($conektaError->details);

                    //Object iteration
                    $conektaError = $error->getConektaMessage();
                    foreach ($conektaError->details as $key) {
                        echo($key->debug_message);
                    }
                } catch (Exception $error) {
                    dd($error);
                }
            }

        } catch (\Exception $e){
            Log::error('ApiController::stripe ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
    }

    public function removeCard($id)
	{
        try {
            if(isset($_GET['user_id']) && $_GET['user_id'] > 0) {

                $user = AppUser::find($_GET['user_id']);

                \Conekta\Conekta::setApiVersion("2.0.0");
                \Conekta\Conekta::setLocale('es');
                \Conekta\Conekta::setApiKey(Admin::find(1)->stripe_api_id);

                $customer = \Conekta\Customer::find($user->id_conekta);
                foreach($customer->payment_sources as $source) {
                    if ($id == $source->id) {
                        $success = $source->delete();
                        return response()->json(['data' => "done"]);
                        break;
                    }
                }
            }

        } catch (\Exception $e){
            Log::error('ApiController::removeCard ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al procesar los datos']);
        }
	}

	public function getStatus($id)
	{
        try {
            $order = Order::find($id);
            $dboy  = Delivery::find($order->d_boy);

            return response()->json(['data' => $order,'dboy' => $dboy]);

        } catch (\Exception $e){
            Log::error('ApiController::getStatus ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
	}

    public function getProducts($category)
    {
        try {

            $products = Item::where('category_id', $category)->get();
            $category = Category::find($category);

            if (!isset($products[0]))
                return response()->json(['success' => false,'message' => 'No se han encontrado resultados para esta categoria']);

            return response()->json(['data' => ['category' => ['id' => $category->id, 'name' => $category->name], 'products' => $products]]);

        } catch (\Exception $e) {
            Log::error('ApiController::getProducts ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al buscar los datos']);
        }
    }

    /*
    |---------------------------------------------
    |@Delete Actual Item Image
    |---------------------------------------------
    */
    public function removeItemImage(Item $item){
        try {
            $file = "upload/item/".$item->img;

            if (isset($item->img) && file_exists($file)){
                unlink($file);
            }

            $item->img = null;
            $item->save();

        } catch (Exception $e){
            Log::error('ApiController::removeItemImage ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al eliminar los datos']);
        }
    }

    /** Helpers */
    public function verifyTimes($store){
        try {

            $now = Carbon::now();
            $op =  Carbon::parse($store['opening_time']);
            $cl =  Carbon::parse($store['closing_time']);

            if ($cl->lt($op) && $op->lt($now))
                return $store;

            if ($now > $op && $now < $cl)
                return $store;

            return null;

        } catch (\Exception $e){
            Log::error('ApiController::verifyTimes ' . $e->getMessage(), ['error_line' => $e->getLine()]);
            return response()->json(['success' => false,'message' => 'Ha ocurrido un error al verificar los horarios']);
        }
    }
}
