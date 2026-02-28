<?php

namespace App\Http\Controllers\Dashboard\Shop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class ShopController extends DashboardController
{
    public function __construct(){
        parent::__construct();
    }
    public function index()
    {
        return view('dashboard.shop.index');
    }

    public function products()
    {
        session()->forget("product");
        $products = \DB::table("products")->orderBy('id', 'desc')->paginate(15);
        return view('dashboard.shop.products')->with('products', $products);
    }

    public function product(Request $request)
    {$product = \DB::table("products")->where("id", $request->id)->first();
            return view('dashboard.shop.product')->with('product', $product);
    }
    public function purchases()
    {
        $purchases = \DB::table("purchases")->join("products", "products.id", "=", "purchases.product_id")->join("users", "users.id", "=", "purchases.user_id")->orderBy('purchases.id', 'desc')->paginate(15);
            return view('dashboard.shop.purchases')->with('purchases', $purchases);

    }

    public function addproduct(Request $request){
        $request->session()->put('product', $request->all());
        $product = session()->get('product');
        return view("dashboard.shop.view-product")->with("product", $product);
    }

    public function saveproduct(Request $request){

        $data = $request->image;
        $product = session()->get('product');

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);

        $data = base64_decode($data);
        $image_name= time().'.png';

        $path = public_path() . "/images/products/" . $image_name;

        file_put_contents($path, $data);

        if(\DB::table("products")->insert(["image"=>$image_name, "name"=>$product['name'], "price"=>$product['price'],
            "items"=>$product["items"], "available"=>$product["items"], "description"=>$product["description"], "date_posted"=>\Carbon\Carbon::now()])){
            session()->forget("product");
            return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Product Created Successfully</p>']);
        }else{
            return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Unable to create product</p>']);
        }
        //return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Successfully uploaded</p>']);
    }

    public function editproduct(Request $request){

        $request->validate([
            "name"=>"required",
            "price"=>"required|min:0",
            "description"=>"required",
            "items"=>"required"
        ]);

        if(\DB::table("products")->where("id", $request->id)->update(["name"=>$request->name, "price"=>$request->price,
            "items"=>$request->items, "available"=>$request->items, "description"=>$request->description])){
            return redirect()->back()->with("success", "Product Updated Successfully");
        }else{
            return redirect()->back()->with("error", "Unable to update product");
        }
    }

    public function editproductimage(Request $request){

        $data = $request->image;

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);

        $data = base64_decode($data);
        $image_name= time().'.png';

        $path = public_path() . "/images/products/" . $image_name;
        $product = \DB::table("products")->where("id", $request->id)->first();

        if(file_exists(public_path()."/images/products/".$product->image)){
            if(unlink(public_path()."/images/products/".$product->image)){
                if(\DB::table("products")->where("id", $request->id)->update(["image"=>$image_name])){
                    file_put_contents($path, $data);
                    return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Product Image Updated Successfully</p>']);
                }else{
                    return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Unable to update product</p>']);
                }
            }
        }
        //return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Successfully uploaded</p>']);
    }

    public function removeproduct(Request $request){
        $product = \DB::table("products")->where("id", $request->id)->first();

        if(file_exists(public_path()."/images/products/".$product->image)){
            if(unlink(public_path()."/images/products/".$product->image)){
                if(\DB::table("products")->where("id", $request->id)->delete()){
                    return redirect()->back()->with('success', 'Product removed Successfully');
                }else{
                    return redirect()->back()->with('error', 'Unable to remove product');
                }
            }
        }
        //return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Successfully uploaded</p>']);
    }
}
