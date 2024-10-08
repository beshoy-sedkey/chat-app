<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ResponsesInterface;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{


    protected $responder;

    public function __construct(ResponsesInterface $responder)
    {
        $this->responder = $responder;
        $this->middleware('auth:api')->only('logout', 'completeRegistration');
    }

    /**
     * Register
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create($request->all());
            if ($token = Auth::guard('api')->attempt($request->only(['name', 'password']))) {
                $data =  [
                    'user' => $user,
                    'authorization' => ['type' => 'bearer', 'token' => $token]
                ];
            }
        } catch (\Throwable $th) {
            return $this->responder->respond(['message' => $th->getMessage()]);
        }

        return  $this->responder->respond(['message' => "Hello {$request->name} You Have Registered Successfully!", 'data' => $data]);
    }

    /**
     * login
     * @param LoginRequest $request
     *
     * @return [type]
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if ($token = Auth::guard('api')->attempt($request->only(['email', 'password']))) {
                $data =  [
                    'user' => Auth::guard('api')->user(),
                    'authorization' => ['type' => 'bearer', 'token' => $token]
                ];
            }
        } catch (\Throwable $th) {
            return $this->responder->respondWithError($th->getMessage());
        }
        return $this->responder->respond(['message' => 'You Have Logged In Successfully' ,'data' => $data]);
    }
}
