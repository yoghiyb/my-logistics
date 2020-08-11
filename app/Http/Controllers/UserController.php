<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['status' => 'success', 'data' => $users], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'identity_id' => 'required|string|unique:users',
            'gender' => 'required',
            'address' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone_number' => 'required|string',
            'role' => 'required',
            'status' => 'required'
        ]);

        $filename = null;
        if ($request->hasFile('photo')) {
            $filename = Str::random(5) . $request->email . '.jpg';
            $file = $request->file('photo');
            $file->move('public/images', $filename);
        }

        User::create([
            'name' => $request->name,
            'identity_id' => $request->identity_id,
            'gender' => $request->gender,
            'address' => $request->address,
            'photo' => $filename, //UNTUK FOTO KITA GUNAKAN VALUE DARI VARIABLE FILENAME
            'email' => $request->email,
            'password' => app('hash')->make($request->password), //PASSWORDNYA KITA ENCRYPT
            'phone_number' => $request->phone_number,
            // 'api_token' => 'test', //BAGIAN INI HARUSNYA KOSONG KARENA AKAN TERISI JIKA USER LOGIN
            'role' => $request->role,
            'status' => $request->status
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    public function edit($id)
    {
        $user = User::find($id);
        return response()->json(['status' => 'success', 'data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'identity_id' => 'required|string|unique:users,identity_id,' . $id,
            'gender' => 'required',
            'address' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'email' => 'required|email|unique:users,identity_id,' . $id,
            'password' => 'required|min:6',
            'phone_number' => 'required|string',
            'role' => 'required',
            'status' => 'required'
        ]);

        $user = User::find($id);

        $password = $request->password != '' ? app('hash')->make($request->password) : $user->password;

        $filename = $user->photo;
        if ($request->hasFile('photo')) {
            $filename = Str::random(5) . $user->email . '.jpg';
            $file = $request->file('photo');
            $file->move('public/images', $filename);

            unlink(base_path('public/images/' . $user->photo));
        }

        $user->update([
            'name' => $request->name,
            'identity_id' => $request->identity_id,
            'gender' => $request->gender,
            'address' => $request->address,
            'photo' => $filename, //UNTUK FOTO KITA GUNAKAN VALUE DARI VARIABLE FILENAME
            'email' => $request->email,
            'password' => $password, //PASSWORDNYA KITA ENCRYPT
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'status' => $request->status
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        unlink(base_path('public/images/' . $user->photo));
        $user->delete();
        return response()->json(['status' => 'success'], 200);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = Str::random(40);
            $user->update(['api_token' => $token]);
            return response()->json(['status' => 'success', 'data' => $token]);
        }
        return response()->json(['status' => 'error']);
    }

    public function sendResetToken(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users'
        ]);

        $user = User::where('email', $request->email)->first();
        $user->update(['reset_token' => Str::random(40)]);

        Mail::to($user->email)->send(new ResetPasswordMail($user));

        return response()->json(['status' => 'success', 'data' => $user->reset_token]);
    }

    public function verifyResetPassword(Request $request, $token)
    {
        $this->validate($request, [
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('reset_token', $token)->first();
        if ($user) {
            $user->update(['password' => app('hash')->make($request->password)]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error']);
    }

    public function getUserLogin(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->update(['api_token' => null]);
        return response()->json(['status' => 'success']);
    }
}
