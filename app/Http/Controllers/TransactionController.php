<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request)
    {
        $transaction=Transaction::create($request->validated());
        return response([
            'success'=>'true',
            'data'=>$transaction,

        ]);
    }
}
