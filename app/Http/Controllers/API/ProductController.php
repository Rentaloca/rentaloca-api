<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Helpers\ResponseFormatter;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if($id)
        {
            $product = Product::find($id);

            if($product)
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
        }

        $product = Product::query();

        if($name)
            $product->where('name', 'like', '%' . $name . '%');

        if($price_from)
            $product->where('price', '>=', $price_from);

        if($price_to)
            $product->where('price', '<=', $price_to);

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function addProduct(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'description' => 'required',
                'picture_path' => 'required|image|max:2048',
                'price' => 'required',
                'size' => 'required',
                'bust' => 'required',
                'waist' => 'required',
                'hips' => 'required',
                'length' => 'required',
                'suitable_for_body_shape' => 'required',
            ]);

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'picture_path' => $this->uploadFile($request->file('picture_path'), 'assets/product', null),
                'price' => $request->price,
                'size' => $request->size,
                'bust' => $request->bust,
                'waist' => $request->waist,
                'hips' => $request->hips,
                'length' => $request->length,
                'suitable_for_body_shape' => $request->suitable_for_body_shape,
            ]);

            return ResponseFormatter::success($product, 'Product Added');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ],'Authentication Failed', 500);
        }
    }

    public function uploadFile(UploadedFile $file, $folder = null, $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        return $file->storeAs(
            $folder,
            $name . "." . $file->getClientOriginalExtension(),
            'gcs'
        );
    }

    public function deleteFile($path = null)
    {
        Storage::disk('gcs')->delete($path);
    }
}
