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
            case 'get_customer':
                return $this->customer_service->getCustomer($request);
                break;
            case 'make_payment':
                return $this->customer_service->makePayment($request);
                break;
                case 'customer_transactions': // New case for customer_transactions
                    return $this->customer_service->customer_transactions($request);
                    break;
                case 'customer_transaction': // New case for customer_transaction
                    return $this->customer_service->customer_transaction($request);
                    break;

        }
    }
}
