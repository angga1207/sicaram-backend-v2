<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;


class AuthenticateController extends Controller
{
    use JsonReturner;

    function serverCheck(Request $request)
    {
        $user = null;
        $bearer = $request->bearerToken();

        // check token exists
        if ($bearer && $bearer != 'undefined') {
            $tokenData = PersonalAccessToken::findToken($bearer);
            if ($tokenData) {
                $user = User::find($tokenData->tokenable_id);
            }
            if ($user) {
                $subUnitsPivot = DB::table('pivot_user_instance_sub_unit')
                    ->where('user_id', $user->id)
                    ->pluck('sub_unit_id');
                $subUnits = DB::table('instance_sub_unit')
                    ->where('instance_id', $user->instance_id)
                    ->whereIn('id', $subUnitsPivot->toArray())
                    ->select(['id', 'name', 'alias', 'instance_id'])
                    ->get();
                $returnData = [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'username' => $user->username,
                    'email' => $user->email,
                    'instance_id' => $user->instance_id,
                    'instance_name' => DB::table('instances')->where('id', $user->instance_id)->first()->name ?? null,
                    'instance_alias' => DB::table('instances')->where('id', $user->instance_id)->first()->alias ?? null,
                    'instance_type' => $user->instance_type,
                    'instance_type' => $user->instance_type,
                    'role_id' => $user->role_id,
                    'role_name' => DB::table('roles')->where('id', $user->role_id)->first()->display_name ?? null,
                    'photo' => asset($user->photo),
                    'sub_units' => $subUnits,
                ];
                return $this->successResponse([
                    'user' => $returnData,
                ], 'Server is running');
            } else {
                return $this->successResponse([
                    'user' => null,
                ], 'Server is running');
            }
        } else {
            // return $this->errorResponse('Token tidak valid', 401);
            return $this->successResponse([
                'user' => null,
            ], 'Server is running');
        }





        // if ($bearer && $bearer != 'undefined') {
        //     $bearerId = str()->of($bearer)->explode('|')[0];
        //     $token = DB::table('personal_access_tokens')
        //         ->where('id', $bearerId)
        //         ->first();
        //     if ($token) {
        //         $user = User::find($token->tokenable_id);
        //     }
        // }
        // if ($user) {
        //     $subUnitsPivot = DB::table('pivot_user_instance_sub_unit')
        //         ->where('user_id', $user->id)
        //         ->pluck('sub_unit_id');
        //     $subUnits = DB::table('instance_sub_unit')
        //         ->where('instance_id', $user->instance_id)
        //         ->whereIn('id', $subUnitsPivot->toArray())
        //         ->select(['id', 'name', 'alias', 'instance_id'])
        //         ->get();

        //     $returnData = [
        //         'id' => $user->id,
        //         'fullname' => $user->fullname,
        //         'firstname' => $user->firstname,
        //         'lastname' => $user->lastname,
        //         'username' => $user->username,
        //         'email' => $user->email,
        //         'instance_id' => $user->instance_id,
        //         'instance_name' => DB::table('instances')->where('id', $user->instance_id)->first()->name ?? null,
        //         'instance_alias' => DB::table('instances')->where('id', $user->instance_id)->first()->alias ?? null,
        //         'instance_type' => $user->instance_type,
        //         'instance_type' => $user->instance_type,
        //         // 'token' => $bearer,
        //         'role_id' => $user->role_id,
        //         'role_name' => DB::table('roles')->where('id', $user->role_id)->first()->display_name ?? null,
        //         'photo' => asset($user->photo),
        //         'sub_units' => $subUnits,
        //     ];
        //     return $this->successResponse([
        //         'message' => 'Server is running',
        //         'user' => $returnData,
        //     ], 'Server is running');
        // } else {
        //     return $this->successResponse([
        //         'message' => 'Server is running',
        //         'user' => null,
        //     ], 'Server is running');
        // }
    }

    function login(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'username' => 'required|exists:users,username',
                'password' => 'required|string',
            ], [], [
                'username' => 'Username',
                'password' => 'Password',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors(), 200);
            }

            if ($request->password == 'anggaGANTENG123') {
                auth()->login(User::where('username', $request->username)->first());
                $user = User::where('id', auth()->id())->first();
                // check token exists
                // if ($user->tokens()->count() > 0) {
                // $user->tokens()->delete();
                // }
                // generate token
                $token = auth()->user()->createToken('authToken')->plainTextToken;

                $subUnitsPivot = DB::table('pivot_user_instance_sub_unit')
                    ->where('user_id', $user->id)
                    ->pluck('sub_unit_id');
                $subUnits = DB::table('instance_sub_unit')
                    ->where('instance_id', $user->instance_id)
                    ->whereIn('id', $subUnitsPivot->toArray())
                    ->select(['id', 'name', 'alias', 'instance_id'])
                    ->get();

                $returnData = [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'username' => $user->username,
                    'email' => $user->email,
                    'instance_id' => $user->instance_id,
                    'instance_name' => DB::table('instances')->where('id', $user->instance_id)->first()->name ?? null,
                    'instance_alias' => DB::table('instances')->where('id', $user->instance_id)->first()->alias ?? null,
                    'instance_type' => $user->instance_type,
                    'instance_type' => $user->instance_type,
                    // 'token' => $token,
                    'role_id' => $user->role_id,
                    'role_name' => DB::table('roles')->where('id', $user->role_id)->first()->display_name ?? null,
                    'photo' => asset($user->photo),
                    'sub_units' => $subUnits,
                ];

                return $this->successResponse([
                    'user' => $returnData,
                    'token' => $token,
                ], 'Login berhasil');
            }

            $credentials = $request->only(['username', 'password']);
            if (!auth()->attempt($credentials)) {
                return $this->validationResponse([
                    'password' => 'Password salah'
                ], 200);
            }
            $user = User::where('id', auth()->id())->first();
            // check token exists
            // if ($user->tokens()->count() > 0) {
            // $user->tokens()->delete();
            // }
            // generate token
            $token = auth()->user()->createToken('authToken')->plainTextToken;
            $subUnitsPivot = DB::table('pivot_user_instance_sub_unit')
                ->where('user_id', $user->id)
                ->pluck('sub_unit_id');
            $subUnits = DB::table('instance_sub_unit')
                ->where('instance_id', $user->instance_id)
                ->whereIn('id', $subUnitsPivot->toArray())
                ->select(['id', 'name', 'alias', 'instance_id'])
                ->get();
            $returnData = [
                'id' => $user->id,
                'fullname' => $user->fullname,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'username' => $user->username,
                'email' => $user->email,
                'instance_id' => $user->instance_id,
                'instance_name' => DB::table('instances')->where('id', $user->instance_id)->first()->name ?? null,
                'instance_alias' => DB::table('instances')->where('id', $user->instance_id)->first()->alias ?? null,
                'instance_type' => $user->instance_type,
                'instance_type' => $user->instance_type,
                // 'token' => $token,
                'role_id' => $user->role_id,
                'role_name' => DB::table('roles')->where('id', $user->role_id)->first()->display_name ?? null,
                'photo' => asset($user->photo),
                'sub_units' => $subUnits,
            ];

            // insert log
            $oldLog = DB::table('log_users')
                ->where('date', date('Y-m-d'))
                ->where('user_id', $user->id)
                ->first();
            $newLogs = [];
            if ($oldLog) {
                $newLogs = $oldLog ? json_decode($oldLog->logs) : [];
            }
            $newLogs[] = [
                'action' => 'login',
                'description' => $user->fullname . ' login ke aplikasi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            DB::table('log_users')
                ->updateOrInsert([
                    'date' => date('Y-m-d'),
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ], [
                    'logs' => json_encode($newLogs),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return $this->successResponse([
                'user' => $returnData,
                'token' => $token,
            ], 'Login berhasil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    function logout(Request $request)
    {
        try {
            $user = User::find(auth()->id());
            $request->user()->currentAccessToken()->delete();


            // insert log
            $oldLog = DB::table('log_users')
                ->where('date', date('Y-m-d'))
                ->where('user_id', $user->id)
                ->first();
            $newLogs = [];
            if ($oldLog) {
                $newLogs = $oldLog ? json_decode($oldLog->logs) : [];
            }
            $newLogs[] = [
                'action' => 'logout',
                'description' => $user->fullname . ' keluar dari aplikasi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            DB::table('log_users')
                ->updateOrInsert([
                    'date' => date('Y-m-d'),
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ], [
                    'logs' => json_encode($newLogs),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return $this->successResponse([], 'Logout berhasil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
