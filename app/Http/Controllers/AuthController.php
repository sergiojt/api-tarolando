<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function store(Request $request){
        $email = User::where('email', $request->email)->first();

        if($email){
            return response()->json([
                "message" => "There is already a registered user with this email."
            ], 400);
        }

        try {
            $user = new User();

            $user->name = (string) $request->name;
            $user->email = (string) $request->email;
            $user->google_id = $request->id;

            $user->save();

            $token = $this->generateToken($user);

            UserToken::create(["token" => $token, "user_id" => $user->id]);
                
            return response()->json([
                "data" => array(
                    "id" => $user->id, 
                    "name" => $user->name, 
                    "email" => $user->email,
                    "token" => $token
                ),
                "message" => "Login completed successfully."
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 400);
        }
    }

    public function findGoogle($id){
        $user = User::where("google_id", $id)->with("companyDefault")->first();

        if($user){
            $token = $this->generateToken($user);

            UserToken::create(["token" => $token, "user_id" => $user->id]);

            return response()->json([
                "data" => array(
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "token" => $token
                ),
                "message" => "Login completed successfully."
            ], 200);
        }

        return response()->json([
            "data" => null,
            "message" => "User listed successfully."
        ], 200);
    }

    public function generateToken($data){
        $data_token = array("id" => $data->id, "name" => $data->name, "email" => $data->email);

        return JWT::encode($data_token, env('APP_KEY'), 'HS256');
    }
}
