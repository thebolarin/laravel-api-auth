<?php

namespace Bitfumes\ApiAuth\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;
use Bitfumes\ApiAuth\Http\Requests\UpdateRequest;
use Bitfumes\ApiAuth\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api')->only('getUser');
        $this->resource = app()['config']['api-auth.resources.user'];
        $this->user     = app()['config']['api-auth.models.user'];
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->user::create($request->all());
        $user->sendEmailVerificationNotification();
        return response(['message' => 'Now verify your email ID to activate your account'], Response::HTTP_CREATED);
    }

    /**
     * Get the authenticated User.
     *
     * @return UserResource
     */
    public function update(UpdateRequest $request)
    {
        $user = auth()->user();
        $user->update($request->all());
        return response([
            'data'=> new $this->resource($user),
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Get the authenticated User.
     *
     * @return UserResource
     */
    public function getUser()
    {
        $user = auth()->user();
        return response([
            'data'=> new $this->resource($user),
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * @param $token
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => auth('api')->user(),
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
}
