<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorCodes;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * Class ApiController
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /** @var ProductService */
    protected $productService;

    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->productService = new ProductService();
    }

    /**
     * Create a Product
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            /** @var \Illuminate\Validation\Validator $validator */
            $validator = $this->productService->validateCreateRequest($request);

            if (!$validator->passes()) {
                return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
            }

            $product = new Product();

            

            $product->name = $request->get('name');
            $product->description = $request->get('description');
            $product->category_id = $request->get('category_id');
            $product->full_price = $request->get('full_price');
            $product->quantity = $request->get('quantity');
            $product->sale_price = $request->get('sale_price');
            $product->photo = $request->get('photo');


            $product->save();

            return $this->returnSuccess($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Get all categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        try {
            $pagParams = $this->getPaginationParams($request);

            $products = Product::where('id', '!=', null);

            $paginationData = $this->getPaginationData($products, $pagParams['page'], $pagParams['limit']);

            $products = $products->offset($pagParams['offset'])->limit($pagParams['limit'])->get();

            return $this->returnSuccess($products, $paginationData);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Get one Product
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {
        try {
            $product = Product::where('id', $id)->first();

            if (!$product) {
                return $this->returnError('errors.product.not_found', ErrorCodes::NOT_FOUND_ERROR);
            }

            return $this->returnSuccess($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Update a Product
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        try {
            $product = Product::where('id', $id)->first();

            if (!$product) {
                return $this->returnError('errors.product.not_found', ErrorCodes::NOT_FOUND_ERROR);
            }
          

            /** @var \Illuminate\Validation\Validator $validator */
            $validator = $this->productService->validateUpdateRequest($request);

            if (!$validator->passes()) {
                return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
            }

            $product->name = $request->get('name');
            $product->description = $request->get('description');
            $product->category_id = $request->get('category_id');
            $product->full_price = $request->get('full_price');
            $product->quantity = $request->get('quantity');
            $product->sale_price = $request->get('sale_price');
            $product->photo = $request->get('photo');


            $product->save();

            return $this->returnSuccess($product);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Delete a Product
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $product = Product::where('id', $id)->first();

            if (!$product) {
                return $this->returnError('errors.product.not_found', ErrorCodes::NOT_FOUND_ERROR);
            }

            $product->delete();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }
}
