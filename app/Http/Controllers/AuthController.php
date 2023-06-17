<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\AuthenticationService;
//use AuthenticationService;


class AuthController extends Controller
{
    public $authentication_service;

    public function __construct()
    {
        $this->authentication_service=new AuthenticationService;

    }
    public function authentication(Request $request)
    {
        switch ($request->method)
        {
            case 'registration':
                return $this->authentication_service->registration($request);
                break;
        }
        switch ($request->method)
        {
            case 'login':
                return $this->authentication_service->login($request);
                break;
        }
    }
}
