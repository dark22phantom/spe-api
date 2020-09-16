<?php
namespace App\Http\Controllers;

use Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller 
{
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60*60 // Expiration time
        ];
        
        return JWT::encode($payload, env('JWT_SECRET'));
    } 

    public function authenticate(Request $request) {

        $params = $request->json()->all();

        $validator = Validator::make($params,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors()
            ]);
        }

        $user = User::where('email', $params['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Email does not exist.',
                'data' => []
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($params['password'], $user->password) || $params['password'] == $user->password) {
            return response()->json([
                'status' => 200,
                'message' => 'Ok',
                'data' => ['token' => $this->jwt($user)]
            ], 200);
        }else{
            return response()->json([
                'status' => 400,
                'message' => 'Password is wrong.',
                'data' => []
            ], 400);
        }
    }
}