<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use Jenssegers\Agent\Agent;
use App\Traits\JsonReturner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PersonalController extends Controller
{
    use JsonReturner;

    function updateFcmToken($id, Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'fcmToken' => 'required|string',
            ], [], [
                'fcmToken' => 'FCM Token',
            ]);
            if ($validate->fails()) {
                return $this->validationResponse($validate->errors());
            }
            $data = User::find($id);
            if (!$data) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }
            $data->fcm_token = $request->fcmToken;
            $data->save();
            return $this->successResponse($data, 'FCM Token berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    function detailMe(Request $request)
    {
        try {
            $data = User::find(auth()->user()->id);
            if (!$data) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }
            $userLogs = DB::table('log_users')
                ->whereBetween('date', [date('Y-m-d', strtotime('-7 days')), date('Y-m-d')])
                ->where('user_id', auth()->id())
                ->latest('date')
                ->paginate(5);
            foreach ($userLogs as $key => $value) {
                $userLogs[$key]->logs = $value->logs ? json_decode($value->logs, true) : [];
            }
            $data = [
                'id' => $data->id,
                'fullname' => $data->fullname,
                'firstname' => $data->firstname,
                'lastname' => $data->lastname,
                'username' => $data->username,
                'email' => $data->email,
                'role_id' => $data->role_id,
                'role_name' => DB::table('roles')->where('id', $data->role_id)->first()->display_name ?? null,
                'instance_id' => $data->instance_id,
                'instance_name' => DB::table('instances')->where('id', $data->instance_id)->first()->name ?? null,
                'instance_type' => $data->instance_type,
                'status' => $data->status,
                'photo' => asset($data->photo),
                'userLogs' => $userLogs,

                'MyPermissions' => $data->MyPermissions(),
            ];

            return $this->successResponse($data, 'Pengguna berhasil diambil');
        } catch (\Exception $e) {
            DB::table('error_logs')
                ->insert([
                    'user_id' => auth()->id() ?? null,
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'log' => $e,
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'status' => 'unread',
                ]);
            // return $this->errorResponse('Terjadi Kesalahan pada Server, Harap Hubungi Admin!');
            return $this->errorResponse($e->getMessage());
        }
    }

    function Logs(Request $request)
    {
        try {
            $data = User::find(auth()->user()->id);
            if (!$data) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }
            $userLogs = DB::table('log_users')
                ->whereBetween('date', [date('Y-m-d', strtotime('-7 days')), date('Y-m-d')])
                ->where('user_id', auth()->id())
                // ->where('user_id', auth()->user()->huwqhasdas)
                ->latest('date')
                ->select(['id', 'ip_address', 'user_agent', 'date', 'logs', 'created_at', 'updated_at'])
                ->paginate(5);

            foreach ($userLogs as $key => $value) {
                // $logs = $value->logs ? json_decode($value->logs, true) : [];
                $logs = $value->logs ? json_decode($value->logs, true) : [];
                $logs = collect($logs);
                if (count($logs) > 0) {
                    $logs = $logs->sortByDesc('created_at');
                }
                $logs = $logs->values()->all();

                $userLogs[$key]->logs = $logs;

                $agent = new Agent();
                $agent->setUserAgent($value->user_agent);
                $userLogs[$key]->device = $agent->device();
                $userLogs[$key]->platform = $agent->platform();
                $userLogs[$key]->browser = $agent->browser();
                $userLogs[$key]->isDesktop = $agent->isDesktop();
                $userLogs[$key]->isMobile = $agent->isMobile();
                $userLogs[$key]->isTablet = $agent->isTablet();
                $userLogs[$key]->isPhone = $agent->isPhone();
            }

            return $this->successResponse($userLogs, 'Pengguna berhasil diambil');
        } catch (\Exception $e) {
            DB::table('error_logs')
                ->insertOrIgnore([
                    'user_id' => auth()->id() ?? null,
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'log' => $e,
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'status' => 'unread',
                ]);
            // return $this->errorResponse('Terjadi Kesalahan pada Server, Harap Hubungi Admin!');
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function notifications(Request $request)
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }

            $notifications = DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->latest('created_at')
                ->paginate(5);

            $return = [];
            $return['current_page'] = $notifications->currentPage();
            foreach ($notifications as $key => $value) {
                $payload = json_decode($value->data, true);
                $fromUser = User::find($payload['byUserId']);
                $uri = null;
                if ($payload['uri']) {
                    $uri = $payload['uri'];
                }
                $return['data'][] = [
                    'id' => $value->id,
                    'photo' => $fromUser ? asset($fromUser->photo) : null,
                    'fullname' => $fromUser ? $fromUser->fullname : 'System',
                    'user_instance' => $fromUser->Instance ? $fromUser->Instance->name : 'System',
                    'user_instance_alias' => $fromUser->Instance ? $fromUser->Instance->alias : 'System',
                    'user_role' => $fromUser->Role ? $fromUser->Role->display_name : 'System',
                    'title' => $payload['title'],
                    'message' => $payload['message'],
                    'time' => Carbon::parse($value->created_at)->isoFormat('D MMM Y - HH:mm') . ' WIB',
                    'date' => $value->created_at,
                    'read' => $value->read_at ? true : false,
                    'modelId' => $payload['modelId'],
                    'type' => $payload['type'],
                    'uri' => $uri,
                ];
            }

            return $this->successResponse($return, 'Notifikasi berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function notificationsLess(Request $request)
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }

            $notifications = DB::table('notifications')
                ->where('notifiable_id', $user->id)
                // ->where('read_at', null)
                ->latest('created_at')
                ->limit(5)
                ->get();

            $return = [];
            foreach ($notifications as $key => $value) {
                $payload = json_decode($value->data, true);
                $fromUser = User::find($payload['byUserId']);

                $uri = null;
                if ($payload['uri']) {
                    $uri = $payload['uri'];
                }
                $return[] = [
                    'id' => $value->id,
                    'profile' => $fromUser ? asset($fromUser->photo) : null,
                    'title' => $payload['title'],
                    'message' => $payload['message'],
                    'time' => Carbon::parse($value->created_at)->diffForHumans(),
                    'read' => $value->read_at ? true : false,
                    'modelId' => $payload['modelId'],
                    'type' => $payload['type'],
                    'uri' => $uri,
                ];
            }

            return $this->successResponse($return, 'Notifikasi berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile());
        }
    }

    function markNotifAsRead($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }

            $notification = DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_id', $user->id)
                ->first();
            if (!$notification) {
                return $this->errorResponse('Notifikasi tidak ditemukan', 200);
            }

            DB::table('notifications')
                ->where('id', $id)
                ->update([
                    'read_at' => date('Y-m-d H:i:s'),
                ]);


            DB::commit();
            return $this->successResponse(null, 'Notifikasi berhasil ditandai sebagai sudah dibaca');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function savePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => 'required|string|different:password',
            'password' => 'required|string',
            'password_confirmation' => 'required|string|same:password',
        ], [], [
            'old_password' => 'Password Lama',
            'password' => 'Password Baru',
            'password_confirmation' => 'Konfirmasi Password',
        ]);

        if ($validate->fails()) {
            return $this->validationResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return $this->errorResponse('Pengguna tidak ditemukan', 200);
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return $this->errorResponse('Password lama tidak sesuai', 200);
            }

            // latest password
            $latestPassword = DB::table('user_password_history')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->first();
            if ($latestPassword) {
                if ($request->password == $latestPassword->old_password) {
                    $validate->errors()->add('password', 'Password Anda sama dengan password sebelumnya, Terakhir Anda mengganti password pada ' . Carbon::parse($latestPassword->created_at)->isoFormat('D MMM Y - HH:mm') . ' WIB');
                    return $this->validationResponse($validate->errors());
                }
            }

            $user->password = bcrypt($request->password);
            $user->save();

            $oldPassword = $request->old_password;
            $newPassword = $request->password;

            DB::table('user_password_history')
                ->insert([
                    'user_id' => $user->id,
                    'old_password' => $oldPassword,
                    'new_password' => $newPassword,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            DB::commit();
            return $this->successResponse(null, 'Password berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    function updateProfile(Request $request)
    {
        $id = auth()->user()->id;
        $validate = Validator::make($request->all(), [
            'fullname' => 'required|string',
            'firstname' => 'nullable|string',
            'lastname' => 'nullable|string',
            'username' => 'required|alpha_dash|alpha_num|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|integer|exists:roles,id',
            'instance_id' => 'nullable|integer|exists:instances,id',
            'instance_type' => 'nullable|string',
            'instance_ids' => 'nullable|array',
            'instance_ids.*' => 'nullable',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10480',
            'password' => 'nullable|string',
            'password_confirmation' => 'nullable|string|same:password',
        ], [], [
            'fullname' => 'Nama lengkap',
            'firstname' => 'Nama depan',
            'lastname' => 'Nama belakang',
            'username' => 'Username',
            'email' => 'Email',
            'role' => 'Role',
            'instance_id' => 'Instance',
            'instance_type' => 'Instance type',
            'instance_ids' => 'Instance',
            'foto' => 'Foto',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi password',
        ]);
        if ($validate->fails()) return $this->validationResponse($validate->errors());

        DB::beginTransaction();
        try {

            $firstname = explode(' ', $request->fullname)[0];
            $lastname = explode(' ', $request->fullname)[1] ?? null;

            $data = User::find($id);
            if (!$data) {
                return $this->errorResponse('Pengguna tidak ditemukan', 404);
            }
            $data->fullname = $request->fullname;
            $data->firstname = $firstname;
            $data->lastname = $lastname;
            $data->username = $request->username;
            $data->email = $request->email ?? $data->email;
            if ($request->foto) {
                $fileName = time();
                $upload = $request->foto->storeAs('images/users', $fileName . '.' . $request->foto->extension(), 'public');
                $data->photo = 'storage/' . $upload;
            }
            $data->save();


            $subUnitsPivot = DB::table('pivot_user_instance_sub_unit')
                ->where('user_id', $data->id)
                ->pluck('sub_unit_id');
            $subUnits = DB::table('instance_sub_unit')
                ->where('instance_id', $data->instance_id)
                ->whereIn('id', $subUnitsPivot->toArray())
                ->select(['id', 'name', 'alias', 'instance_id'])
                ->get();

            $returnData = [
                'id' => $data->id,
                'fullname' => $data->fullname,
                'firstname' => $data->firstname,
                'lastname' => $data->lastname,
                'username' => $data->username,
                'email' => $data->email,
                'instance_id' => $data->instance_id,
                'instance_name' => DB::table('instances')->where('id', $data->instance_id)->first()->name ?? null,
                'instance_alias' => DB::table('instances')->where('id', $data->instance_id)->first()->alias ?? null,
                'instance_type' => $data->instance_type,
                'instance_type' => $data->instance_type,
                // 'token' => $bearer,
                'role_id' => $data->role_id,
                'role_name' => DB::table('roles')->where('id', $data->role_id)->first()->display_name ?? null,
                'photo' => asset($data->photo),
                'sub_units' => $subUnits,
            ];

            DB::commit();
            return $this->successResponse($returnData, 'Informasi Anda berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }
}
