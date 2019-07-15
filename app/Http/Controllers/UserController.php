<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorCodes;
use App\Helpers\JWT;
use App\Models\User;
use App\Models\UserToken;
use App\Services\EmailService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\v1
 */
class UserController extends Controller
{
    /** @var UserService */
    private $userService;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userService = new UserService();
    }

    /**
     * Login the user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        if ($request->has('rememberToken')) {
            return $this->loginWithRememberToken($request);
        }

        try {
            /** @var Validator $validator */
            $validator = $this->userService->validateLoginRequest($request);

            if (!$validator->passes()) {
                return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
            }

            $user = $this->userService->loginUser($request->only('email', 'password'));

            if (!$user) {
                return $this->returnError('Invalid credentials!', ErrorCodes::REQUEST_ERROR);
            }

            $data = [
                'user' => $user,
                'token' => JWT::generateToken([
                    'id' => $user->id
                ])
            ];

            if ($request->has('remember')) {
                $data['rememberToken'] = $this->userService->generateRememberMeToken($user->id);
            }

            return $this->returnSuccess($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Login with remember token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function loginWithRememberToken(Request $request)
    {
        try {
            /** @var \Illuminate\Validation\Validator $validator */
            $validator = $this->userService->validateTokenLoginRequest($request);

            if (!$validator->passes()) {
                return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
            }

            $rememberToken = $request->get('rememberToken');

            $user = $this->userService->loginUserWithRememberToken($rememberToken);

            if (!$user) {
                return $this->returnError('Invalid remember token!', ErrorCodes::REQUEST_ERROR);
            }

            $this->userService->updateRememberTokenValability($rememberToken);

            $data = [
                'user' => $user,
                'token' => JWT::generateToken([
                    'id' => $user->id
                ])
            ];

            return $this->returnSuccess($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Return user
     *
     * @return JsonResponse
     */
    public function getUser()
    {
        try {
            $user = Auth::user();

            return $this->returnSuccess($user);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }


/**
     * Create a category
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if($user->type == 1){
            try {
                /** @var \Illuminate\Validation\Validator $validator */
                $validator = $this->userService->validateCreateRequest($request);

                if (!$validator->passes()) {
                    return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
                }

                $user = new User();

                

                $user->name = $request->get('name');
                $user->email = $request->get('email');
                $user->password = Hash::make($request->get('password'));
                $user->type = $request->get('type');
                

                $user->save();

                return $this->returnSuccess($user);
            } catch (\Exception $e) {
                return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
            }
        }
    }




    public function update($id, Request $request)
    {

        $user = Auth::user();

        try {
            /** @var \Illuminate\Validation\Validator $validator */
            $validator = $this->userService->validateCreateRequest($request);

            if (!$validator->passes()) {
                return $this->returnError($validator->messages(), ErrorCodes::REQUEST_ERROR);
            }

                if($user->type == 1){
                    
                        $user = User::where('id', $id)->first();

                        $user->name = $request->get('name');
                        $user->email = $request->get('email');
                        $user->password =Hash::make($request->get('password')); //$request->get('password');
                        
                        $user->type = $request->get('type');

                        $user->save();

                        return $this->returnSuccess($user);
                    
                } else {
                    $user->name = $request->get('name');
                    $user->email = $request->get('email');
                    $user->password =Hash::make($request->get('password'));
                }

            } catch (\Exception $e) {
                return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
            }
    }


    /**
     * Delete a User
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $user = Auth::user();
        if($user->type == 1){
            try {
                $user = User::where('id', $id)->first();

                if (!$user) {
                    return $this->returnError('errors.user.not_found', ErrorCodes::NOT_FOUND_ERROR);
                }

                $user->delete();

                return $this->returnSuccess();
            } catch (\Exception $e) {
                return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
            }
        } else {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);  
        }
    }




    /**
     * Logout the user, delete remember me token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($request->has('rememberToken')) {
                UserToken::where('token', $request->get('rememberToken'))
                    ->where('user_id', $user->id)
                    ->where('type', UserToken::TYPE_REMEMBER)
                    ->delete();
            }

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Reset user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            //validate request //email, valid, exists user, email

            $user = User::where('email', $request->get('email'))->first();

            $user->forgot_code = str_random(6);
            $user->forgot_generated = Carbon::now()->format('Y-m-d H:i:s');

            $user->save();

            $emailService = new EmailService();

            $emailService->sendForgotPasswordCode($user);

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }

    /**
     * Change reset user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            //validate request //email, code, valid, exists user, email

            $user = User::where('email', $request->get('email'))
                ->where('forgot_code', $request->get('code'))
                ->first();

            if (!$user) {
                //codul nu e bun
            }

            if (Carbon::parse($user->forgot_generated)->addHour() < Carbon::now()) {
                //codul a expirat
            }

            $user->password = Hash::make($request->get('password'));
            $user->forgot_code = '';

            $user->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), ErrorCodes::FRAMEWORK_ERROR);
        }
    }
}
