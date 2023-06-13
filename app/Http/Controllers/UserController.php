<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $user = User::paginate(10);

        return view('users.index', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request)
    {
        $data = $request->all();

        $data['profile_photo_path'] = $this->uploadFile($request->file('profile_photo_path'), 'assets/user', null);

        User::create($data);

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(User $user)
    {
        return view('users.edit',[
            'item' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        // $data = $request->all();

        // $path = $request->user()->profile_photo_path;
        // $request->hasFile('profile_photo_path') ? $this->deleteFile($path) : $request->user()->profile_photo_path;

        $user->update([
            'name' => $request->name != null? $request->name : $request->user()->name,
            'email' => $request->email != null? $request->email : $request->user()->email,
            'phone_number' => $request->phone_number != null? $request->phone_number : $request->user()->phone_number,
            // 'profile_photo_path' => $request->file('profile_photo_path')->store('assets/user', 'public'),
            'profile_photo_path' => $this->uploadFile($request->file('profile_photo_path'), 'assets/user', null),
            'address' => $request->address != null? $request->address : $request->user()->address,
            'weight' => $request->weight != null? $request->weight : $request->user()->weight,
            'height' => $request->height != null? $request->height : $request->user()->height,
            'top_size' => $request->top_size != null? $request->top_size : $request->user()->top_size,
            'bottom_size' => $request->bottom_size != null? $request->bottom_size : $request->user()->bottom_size,
            'password' => Hash::make($request->password),
            'roles' => $request->roles,
        ]);

        // if($request->file('picturePath'))
        // {
        //     $data['profile_photo_path'] = $this->uploadFile($request->file('profile_photo_path'), 'assets/user', null);
        // }

        // $user->update($data);

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        $user->delete();
        $this->deleteFile($request->user()->profile_photo_path);

        return redirect()->route('users.index');
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