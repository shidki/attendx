<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\user_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;

class authController extends Controller
{
    public function login(Request $request){
        try {
            $credentials = $request->validate([
                'email' => "required|max:255|email",
                'password'=> "required",
            ]);

            $user = User::where("username",'=',$request->username)->first();
            if(!$user){
                return response()->json([
                    "success" => false,
                    'message' => "username tidak tersedia"
                ],422);
            }
            if($user->email_verified_at == null){
                return response()->json([
                    "success" => false,
                    'message' => "Email belum terverifikasi"
                ],422);
            }

            if(! Hash::check($request->password, $user->password)){
                return response()->json([
                    "success" => false,
                    'message' => "password salah !"
                ],422);
            }
            $user->tokens()->delete();

            
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                "success" => true,
                "message" => "Login Berhasil",
                "user" => $user,
                "token" => $token,
            ],200);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                "message" => $e->getMessage()
            ],422);
        }
    }

    public function register(Request $request){
        // dd(5);
        try {
            $credentials = $request->validate([
                'username' => "required|max:255",
                'full_name' => "required|max:255",
                'email' => "required|max:255|email",
                'password'=> "required",
                'confirm_password'=> "required",
            ]);

            $user = user_profile::where('email','=',$request->email)->first();
            $username = User::where('username','=',$request->username)->first();
            
            if($user){
                return response()->json([
                    "success" => false,
                    'message' => "email tersedia"
                ],422);
            }
            if($username){
                return response()->json([
                    "success" => false,
                    'message' => "username tersedia"
                ],422);
            }

            if($request->password !== $request->confirm_password){
                return response()->json([
                    "success" => false,
                    'message' => "password tidak sesuai"
                ],422);
            }

            $registerUser = DB::table("users")->insert([
                "username" => $request->username,
                "password" => Hash::make($request->password),
            ]);

            if($registerUser){
                // get id user
                $getID = User::where("username",'=',$request->username)->first();

                // get id employee
                $IdTerbesar = user_profile::orderByDesc("employee_id")->first();
                $idEmployee = $IdTerbesar ? $IdTerbesar->employee_id + 1 : 100;
                $registerProfile = DB::table("users_profile")->insert([
                    "user_id" => $getID->id,
                    "name" => $request->full_name,
                    "email" => $request->email,
                    "employee_id" => $idEmployee,
                ]);
                if($registerProfile){
                    return response()->json([
                        "success" => true,
                        "message" => "Success register account",
                        "user" => $request->all()
                    ],201);
                }else{
                    $deleteUser = User::where("username",'=',$request->username)->delete();
                    if($deleteUser){
                        return response()->json([
                            "success" => false,
                            "message" => "Error register account",
                        ],422);
                    }else{
                        return response()->json([
                            "success" => false,
                            "message" => "Error deleting user",
                        ],422);
                    }
                }
            }


        } catch (\Exception $e) {
             //throw $th;
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ],422);
        }
    }
}
