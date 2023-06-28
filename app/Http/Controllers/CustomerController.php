<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Services\SettingService;

class CustomerController extends Controller
{
    public $customer_service;
    public $setting_service;

    public function __construct()
    {
        $this->customer_service = new CustomerService;
        $this->setting_service = new SettingService;
    }

    public function api(Request $request)
    {
        switch ($request->method) {
            case 'update':
                return $this->customer_service->updateCustomer($request);
                break;
            case 'update_settings':
                return $this->setting_service->updateUserSettings($request);
                break;
            case 'get_customer':
                return $this->customer_service->getCustomer($request);
                break;
            case 'make_payment':
                return $this->customer_service->makePayment($request);
                break;
            case 'customer_transactions':
                return $this->customer_service->customer_transactions($request);
                break;
            case 'customer_transaction':
                return $this->customer_service->customer_transaction($request);
                break;
        }
    }
}
