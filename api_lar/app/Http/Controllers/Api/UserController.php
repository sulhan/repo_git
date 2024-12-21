<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use App\Models\Orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\send_email;

class UserController extends Controller
{
    public function index()
	{
	
	}
	public function create(Request $request)
	{
		 $validator = Validator::make($request->all(), [
            'name'      => 'required|string|min:3|max:50',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:8'
        ]);

    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
		$usr = new User;
		$usr->email = $request->email;
		$usr->password = $request->password;
		$usr->name = $request->name;
		$usr->save(); //save to db
		$name = $request->name;
		Mail::to($request->email)->send(new send_email($name));	//send email to user
		Mail::to(env('email_admin'))->send(new send_email($name)); //send email to admin
		return response()->json([
		"id" => $usr->id,
		"email" => $usr->email,
		"name" => $usr->name,
		"created_at" => $usr->created_at,
	
		], 201);
		
		
	}
	
	
		public function search(Request $request)
	{
		 
		 $validator = Validator::make($request->all(), [
            'search'      => 'nullable|string',
            'page'     => 'nullable|integer',
            'sortBy'  => 'in:name,email,created_at'
        ]);

    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
		$cari = $request->search;
		$page = $request->page; 
		$sortBy = $request->sortBy;
		
		if(empty($page)){
		$page = 1;
		}
		if(empty($sortBy)){
		$sortBy = "created_at";
		}
	
		$result = User::select('users.id','users.email','users.name','users.created_at', DB::raw('COUNT(orders.id) as orders_count'))
		->where('name', 'like', '%'.$cari.'%')
		->orwhere('email', 'like', '%'.$cari.'%')	
		->leftjoin('orders', 'users.id', '=', 'orders.user_id')
		->orderBy($sortBy)
		->groupBy('users.id')
        ->paginate( 3,['*'],'page',$page);  //3 data per page
		
        $jsondata = $result->toArray();		
		$dat = $jsondata['data'];
		$out['page'] = $jsondata['current_page'];
		$out['users']=  $dat;
		   
        return $out;
      
		
	}
}
