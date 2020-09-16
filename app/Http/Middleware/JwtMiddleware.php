<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header('token');
        
        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'status' => 401,
                'message' => 'You need token to access this API.',
                'data' => []
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Token is expired.',
                'data' => []
            ], 400);
        } catch(Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Token is not valid!',
                'data' => []
            ], 400);
        }

        $user = User::find($credentials->sub);
       
        $request->auth = $user;
        return $next($request);
    }
}