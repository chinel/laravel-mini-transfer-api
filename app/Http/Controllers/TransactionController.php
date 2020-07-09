<?php

namespace App\Http\Controllers;


use App\Balance;
use App\Transaction;
use Illuminate\Http\Request;
use JWTAuth;

class TransactionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
}
