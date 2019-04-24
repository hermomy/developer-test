<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Get;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Session;

 use App\Models\Users;
 use App\Models\PurchaseOrder;
 use App\Models\Items;
 use App\Models\Inventory;
// use App\Models\Brand;
// use App\Models\OrderDetails;

class HermoBackendController extends Controller
{
  public function index()
  {

   
    return \View::make('master_template')
    ->nest('content','login');
  }

  public function login_process(){  
  	$name = Input::get('name');
  	$password = Input::get('password');
  	
    $sqllogin = DB::table('users')
    ->where('name','=',$name)
    ->Where('password','=',$password)
    ->count();
    
   
    
    if($sqllogin > 0)
    {
         
        // return \View::make('master_template')
        // ->nest('content','mainpage');

      Session::put('name', $name);

      return Redirect::to(\URL::route('mainpage'));
    }

    else{
           return redirect()->route('login')
        ->with('error','Your username and Password are wrong or your account is not existed' );
    }
  	
  
  }

  public function mainpage(){


  $namesessions = Session::get('name');
   if (Session::has('name'))
   {  

      return \View::make('master_template')
      ->nest('content','mainpage',array('namesessions' => $namesessions));
   }
   else
   {
      return Redirect::to(\URL::route('login'));
   }
    
  }

  public function list_purchase_order(){

    $mytime = date("Y-m-d");
    $countPO = DB::table('purchaseorders')->count();
    $totalCountPO = 0;
    if($countPO == 0)
    {
      $totalCountPO = 1;
    }
    $mytimeremove = date("Ymd");
    $po_number = "PO".$mytimeremove.$totalCountPO;
    $countPO = DB::table('purchaseorders')->count();
    $namesessions = Session::get('name');
    $requestor = DB::table('requestor')->select('id', 'name')->get();
    $vendor = DB::table('vendor')->select('id', 'name')->get();$requestor = DB::table('requestor')->select('id', 'name')->get();
    $credictterms =  DB::table('terms')->select('id', 'name')->get();
    $listpurchaseorders = DB::table('purchaseorders')
    ->join('requestor', 'requestor.id', '=', 'purchaseorders.requestor_id')
    ->join('vendor', 'vendor.id', '=', 'purchaseorders.vendor_id')
    ->select('purchaseorders.*', 'requestor.name as requestorname', 'vendor.name as vendorname')
    ->get();
       return \View::make('master_template')
      ->nest('content','list_purchase_order',array('namesessions' => $namesessions,'datetoday' => $mytime , 'ponumber' => $po_number ,'requestor' => $requestor , 'vendor' => $vendor , 'terms' => $credictterms , 'listpurchaseorders' => $listpurchaseorders));
      
  }

  public function add_purchase_order (){

    $po         = new PurchaseOrder;
    $po->po_number = Input::get('po_number');
    $po->requestor_id = Input::get('requestor');
    $po->vendor_id = Input::get('vendor');
    $po->po_date = Input::get('po_date');
    $po->order_date = Input::get('order_date');
    $po->receive_date = Input::get('receive_date');
    $po->cost = Input::get('cost');
    $po->status_fufilment = "unpaid";
    $po->credict_terms = Input::get('terms');
    $po->po_description = Input::get('po_description');
    $po->comments = Input::get('comments');
    $po->status = "new";
    $po->save();


    return redirect()->route('list_purchase_order')
    ->with('success','Purchase Order Has Been Create Sucessfully');


  }

  
  public function edit_purchase_order($po_number){

     $namesessions = Session::get('name');
     $sqlPO = DB::table('purchaseorders')
    ->join('requestor', 'requestor.id', '=', 'purchaseorders.requestor_id')
    ->join('vendor', 'vendor.id', '=', 'purchaseorders.vendor_id')
    ->select('purchaseorders.*', 'requestor.name as requestorname', 'vendor.name as vendorname')
    ->where('purchaseorders.po_number' , $po_number)
    ->first();

    $sqlstockitems = DB::table('stock_items')
    ->select('id', 'name')
    ->get();

    $listItemsLine= DB::table('items')
    ->join('stock_items', 'stock_items.id', '=', 'items.stock_items_id')
    ->select('items.*', 'stock_items.id as stockitemsid' , 'stock_items.name' , 'stock_items.skus' , 'stock_items.price')
    ->get();






     return \View::make('master_template')
       ->nest('content','edit_purchase_order',array('namesessions' => $namesessions , 'PODetails' => $sqlPO , 'stockitems' => $sqlstockitems ,'listItemsLine' => $listItemsLine)); 
  }


  public function add_lineItems(){

    $po_id = Input::get('poid');
    $quantity = Input::get('quantity');
    $id = Input::get('items_id');
   
    $priceperunit = DB::table('stock_items')
    ->select('price')
    ->where('id',$id)
    ->first();

    $costitemsperunit = $priceperunit->price;
    $cost = $costitemsperunit * $quantity;
    $po_number= DB::table('purchaseorders')
    ->select('po_number')
    ->where('id', $po_id)
    ->first();

    $items         = new Items;
    $items->stock_items_id = Input::get('items_id');
    $items->quantity = Input::get('quantity');
    $items->cost = $cost;
    $items->purchaseorder_id = Input::get('poid');

    $items->save();

    $Sumcost = DB::table('items')
    ->where('purchaseorder_id', $po_id)
    ->sum('cost');

    $updatecost = DB::table('purchaseorders')
            ->where('id', $po_id)
            ->update(['cost' => $Sumcost]);


     return redirect()->route('edit_purchase_order',$po_number->po_number)
     ->with('success','Items Line Has Been Create Sucessfully');

  }


  public function update_purchase_order(){

    $statusfufilment = Input::get('status_fufilment');
    $receivedate = Input::get('receive_date');
    $po_id = Input::get('poidupdatepo');

     $po_number= DB::table('purchaseorders')
    ->select('po_number')
    ->where('id', $po_id)
    ->first();

     $updatecost = DB::table('purchaseorders')
            ->where('id', $po_id)
            ->update(['status_fufilment' => $statusfufilment , 'receive_Date' => $receivedate]);


     return redirect()->route('edit_purchase_order',$po_number->po_number)
     ->with('success','Purchase order Has Been Update Sucessfully');
  }

  public function update_quantity_items(){

    $po_id = Input::get('idpo');
    $iditem = Input::get('iditem');
    $itemname = Input::get('itemname');
    $quantity_received = Input::get('quantity_received');

    $po_number = DB::table('purchaseorders')
    ->select('po_number')
    ->where('id', $po_id)
    ->first();

     $updatecost = DB::table('items')
     ->where('id', $iditem)
     ->update(['quantity_received' => $quantity_received ]);

     return redirect()->route('edit_purchase_order',$po_number->po_number)
      ->with('success','Quantity Receive has been Update Sucessfully');

  }

  public function list_items(){

  $namesessions = Session::get('name');
   
      

    $sqlListItems = DB::table('items')
    ->join('stock_items', 'stock_items.id', '=', 'items.stock_items_id')
    ->join('purchaseorders','purchaseorders.id','=','items.purchaseorder_id')
    ->select('items.*', 'stock_items.name', 'stock_items.description','stock_items.category','stock_items.price','stock_items.skus','purchaseorders.po_number')
    ->get();
    
     return \View::make('master_template')
      ->nest('content','list_items',array('ListItems' => $sqlListItems , 'namesessions' => $namesessions));
  }

  public function add_register_locationtagging(){

    $iditem = Input::get('items_id');
    $statusinventory = "Register Inventory";
    $inventory         = new Inventory;
    $inventory->stock_items_id = Input::get('stock_items_id');
    $inventory->category = Input::get('category');
    $inventory->inventory_received = Input::get('inventory_received');
    $inventory->items_id = Input::get('items_id');
    $inventory->location_tagging = Input::get('location_tagging'); 
    $inventory->starting_inventory = 0;
    $inventory->save();

    $updateItemsStatus = DB::table('items')
     ->where('id', $iditem)
     ->update(['status_inventory' => $statusinventory ]);

    return redirect()->route('list_items')
    ->with('success','Your Item Already Register');
   

  }
}

