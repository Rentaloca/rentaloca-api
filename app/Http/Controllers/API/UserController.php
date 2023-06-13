<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use PasswordValidationRules;

    /**
     * @param Request $request
     * @return mixed
     */

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data profile user berhasil diambil');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ],'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();
            if ( ! Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ],'Authentication Failed', 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules()
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                // 'profile_photo_path' => $request->file('profile_photo_path')->store('assets/user', 'public'),
                'profile_photo_path' => null,
                'address' => $request->address,
                'weight' => $request->weight,
                'height' => $request->height,
                'top_size' => $request->top_size,
                'bottom_size' => $request->bottom_size,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ],'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token,'Token Revoked');
    }

    public function updateProfile(Request $request)
    {

        $user = Auth::user();

        $path = $request->user()->profile_photo_path;
        $request->hasFile('profile_photo_path') ? $this->deleteFile($path) : null;

        $user->update([
            'name' => $request->name != null? $request->name : $request->user()->name,
            'email' => $request->email != null? $request->email : $request->user()->email,
            'phone_number' => $request->phone_number != null? $request->email : $request->user()->email,
            // 'profile_photo_path' => $request->file('profile_photo_path')->store('assets/user', 'public'),
            'profile_photo_path' => $request->file('profile_photo_path') != null ? $this->uploadFile($request->file('profile_photo_path'), 'assets/user', null) : $request->user()->profile_photo_path,
            'address' => $request->address != null? $request->address : $request->user()->address,
            'weight' => $request->weight != null? $request->weight : $request->user()->weight,
            'height' => $request->height != null? $request->height : $request->user()->height,
            'top_size' => $request->top_size != null? $request->top_size : $request->user()->top_size,
            'bottom_size' => $request->bottom_size != null? $request->bottom_size : $request->user()->bottom_size,
            'password' => $request->password != null? Hash::make($request->password) : $request->user()->password,
        ]);

        return ResponseFormatter::success($user,'Profile Updated');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['error'=>$validator->errors()], 'Update Photo Fails', 401);
        }

        if ($request->file('file')) {

            // $file = $request->file->store('assets/user', 'public');
            $file = $this->uploadFile($request->file('profile_photo_path'), 'assets/user', null);

            //store your file into database
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file],'File successfully uploaded');
        }
    }

    public function uploadFile(UploadedFile $file, $folder = null, $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        return $file->storeAs(
            $folder,
            $name . "." . $file->getClientOriginalExtension(),
            'gcs'
        );
    }

    public function deleteFile($path = null)
    {
        Storage::disk('gcs')->delete($path);
    }

}
