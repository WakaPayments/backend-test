<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    public $customer_service;

    public function __construct()
    {
        $this->customer_service = new CustomerService;
    }
    public function api(Request $request)
    {
        switch ($request->method) {
            case 'update':
                return $this->customer_service->updateCustomer($request);
                break;
            case 'get':
                return $this->customer_service->getCustomer($request);
                break;
        }
    }
}
