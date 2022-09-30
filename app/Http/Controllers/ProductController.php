<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        \DB::statement("SET SQL_MODE=''");

        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;

        $query = Product::query();

        $query->when($title, function ($q, $title) {
            return $q->where('title', 'like', '%' . $title . '%');
        });
        $query->when($date, function ($q, $date) {
            $date = Date("Y-m-d", strtotime($date));
            return $q->whereDate('created_at', $date);
        });

        $query->when($price_from,  function ($q) use ($price_from, $price_to) {

            return $q->whereHas('product_variant_prices', function ($q) use ($price_from, $price_to) {
                $q->whereBetween('price', [$price_from, $price_to]);
            });
        });

        $query->when($variant,  function ($q) use ($variant) {
            return $q->whereHas('product_variant_prices', function ($q) use ($variant) {
                $q->where('product_variant_one', $variant);
                $q->Orwhere('product_variant_two', $variant);
                $q->Orwhere('product_variant_three', $variant);
            });
        });



        $variants = Variant::all();
        $variant_list = [];
        foreach($variants as $key=>$val){
            $product_variants = ProductVariant::where('variant_id',$val['id'])->groupBy('variant')->get();
            $variant_list[] = $val;
            $variant_list[$key]['product_variants'] = $product_variants;
        }
        $products = $query->with('product_variant_prices','product_variant_prices.variant_one','product_variant_prices.variant_two','product_variant_prices.variant_three')->paginate(2);

        return view('products.index', compact('products','variant_list'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'title' => 'required',
            'sku ' => 'required',
        ])->validate();

        \DB::beginTransaction();
        try {
            $data = [];

            $data['title'] = $request->title;
            $data['sku'] = $request->sku;
            $data['description'] = $request->description;


            $product = Product::create($data);
            foreach($request->product_variant as $key=>$val){
                foreach($val['tags'] as $key=>$vl){
                    $data = [];
                    $data['variant'] = $vl;
                    $data['variant_id'] = $val['option'];
                    $data['product_id'] =  $product['id'];
                    $product_variant = ProductVariant::create($data);
                }
            }

            foreach($request->product_variant_prices as $key=>$val){
                $variant = $val['title'];
                $product_variant_data = explode('/',trim($variant));

                $product_variant_one = ProductVariant::where('variant',$product_variant_data[0])->first();
                $product_variant_two = ProductVariant::where('variant',$product_variant_data[1])->first();
                $product_variant_three = ProductVariant::where('variant',$product_variant_data[2])->first();

                $product_variant_one_id = "";
                $product_variant_two_id = "";
                $product_variant_three_id = "";

                if($product_variant_one){
                    $product_variant_one_id = $product_variant_one['id'];
                }
                if($product_variant_two){
                    $product_variant_two_id = $product_variant_two['id'];
                }
                if($product_variant_three){
                    $product_variant_three_id = $product_variant_three['id'];
                }

                $data = [];
                $data['product_variant_one'] = $product_variant_one_id;
                $data['product_variant_two'] = $product_variant_two_id;
                $data['product_variant_three'] =  $product_variant_three_id;
                $data['price'] =  $val['price'];;
                $data['stock'] =  $val['stock'];
                $data['product_id'] =  $product['id'];
                $product_variant = ProductVariantPrice::create($data);

            }

            \DB::commit();

            $response =  'Save Successfully';
            return response()->json($response);
        } catch (\Exception $e) {
            \DB::rollback();

            $response = $e->getMessage();
        }


        return response()->json($response);

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)

    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
