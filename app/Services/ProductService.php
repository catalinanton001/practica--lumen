<?php


namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class CategoryService
 *
 * @package App\Services
 */
class ProductService
{
    public function validateCreateRequest(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'full_price' => 'required',
            'quantity' => 'required',
            'sale_price' => 'required',
            // 'photo' => 'required',
        ];

        $messages = [
            'name.required' => 'errors.name.required',
            'description.required' => 'errors.description.required',
            'category_id.required' => 'errors.category_id.required',
            'full_price.required' => 'errors.full_price.required',
            'quantity.required' => 'errors.quantity.required',
            'sale_price.required' => 'errors.sale_price.required',
            // 'photo.required' => 'errors.photo.required',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    public function validateUpdateRequest(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'full_price' => 'required',
            'quantity' => 'required',
            'sale_price' => 'required',
            // 'photo' => 'required',
        ];

        $messages = [
            'name.required' => 'errors.name.required',
            'description.required' => 'errors.description.required',
            'category_id.required' => 'errors.category_id.required',
            'full_price.required' => 'errors.full_price.required',
            'quantity.required' => 'errors.quantity.required',
            'sale_price.required' => 'errors.sale_price.required',
            // 'photo.required' => 'errors.photo.required',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}
