<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileRequest;
use App\Models\SignalBit\UserPassword;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProfileRequest $request, $id)
    {
        $validatedRequest = $request->validated();

        if ($validatedRequest['password'] && $validatedRequest['password'] != '') {
            $updateArray = array(
                'FullName' => $validatedRequest['full_name'],
                'Password' => $validatedRequest['password'],
                'password_encrypt' => Hash::make($validatedRequest['password']),
            );
        } else {
            $updateArray = array(
                'FullName' => $validatedRequest['full_name']
            );
        }

        $updateProfile = UserPassword::where('line_id', $id)->
            update($updateArray);

        if ($updateProfile) {
            session(['user_id' => Auth::user()->line_id, 'user_username' => Auth::user()->username, 'user_name' => Auth::user()->FullName]);

            return array(
                'status' => '200',
                'message' => 'Berhasil mengubah profil',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Gagal mengubah profil',
            'redirect' => '',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
