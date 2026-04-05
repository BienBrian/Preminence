<?php

namespace App\Http\Controllers\Dashboard\Shop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends DashboardController
{
    public function __construct(){
        parent::__construct();
        $this->middleware(['permission:View Shop']);
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
        $product = session()->get('product');

        $result = $this->processBase64Image($request->image, public_path('images/products'));
        if (isset($result['error'])) {
            return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> ' . e($result['error']) . '</p>']);
        }

        $image_name = $result['filename'];

        if(\DB::table("products")->insert(["image"=>$image_name, "name"=>$product['name'], "price"=>$product['price'],
            "items"=>$product["items"], "available"=>$product["items"], "description"=>$product["description"], "date_posted"=>\Carbon\Carbon::now()])){
            session()->forget("product");
            return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Product Created Successfully</p>']);
        }else{
            return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Unable to create product</p>']);
        }
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
        $result = $this->processBase64Image($request->image, public_path('images/products'));
        if (isset($result['error'])) {
            return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> ' . e($result['error']) . '</p>']);
        }

        $image_name = $result['filename'];
        $path = public_path('images/products/' . $image_name);
        $product = \DB::table("products")->where("id", $request->id)->first();

        if(file_exists(public_path()."/images/products/".$product->image)){
            if(unlink(public_path()."/images/products/".$product->image)){
                if(\DB::table("products")->where("id", $request->id)->update(["image"=>$image_name])){
                    return response()->json(['success'=>'<p class="text-center text-success"><i class="fas fa-check"></i> Product Image Updated Successfully</p>']);
                }else{
                    return response()->json(['success'=>'<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Unable to update product</p>']);
                }
            }
        }
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
        return redirect()->back()->with('error', 'Unable to remove product');
    }

    /**
     * Process a base64-encoded image string and save it to disk.
     * Validates MIME type and file contents.
     */
    private function processBase64Image(?string $base64String, string $directory): array
    {
        if (empty($base64String)) {
            return ['error' => 'No image provided'];
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            return ['error' => 'Invalid image format'];
        }

        $imageType = strtolower($matches[1]);
        $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (!in_array($imageType, $allowedTypes, true)) {
            return ['error' => 'Invalid image type. Allowed: jpeg, png, gif, webp'];
        }

        $data = substr($base64String, strpos($base64String, ',') + 1);
        $data = base64_decode($data, true);

        if ($data === false) {
            return ['error' => 'Invalid base64 data'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $data);
        finfo_close($finfo);

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($mimeToExt[$mimeType])) {
            return ['error' => 'Invalid image content'];
        }

        $extension = $mimeToExt[$mimeType];
        $filename = time() . '_' . Str::random(8) . '.' . $extension;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = rtrim($directory, '/') . '/' . $filename;
        file_put_contents($path, $data);

        return ['success' => true, 'filename' => $filename];
    }
}
