<?php

namespace App\Http\Controllers;


use App\Balance;
use App\Transaction;
use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }



    /* This account is called whenever a user wants to transfer funds to his account or another person's account*/
    public function transferFunds(Request $request){
        $this->validate($request, [
            'to' => 'required',
            'amount' => 'required',
        ]);

        //this checks if the account number you are trying to pay into exist
        $checkAccount = Balance::where('account', $request->to)->first();
        if (!empty($checkAccount)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the account number you are trying to pay into does not exist'
            ], 400);
        }

        //check if you have enough more than or equal to the amount in your account balance
        $checkAccountBalance = Balance::select('id','balance', 'account')->where('user_id', \Auth::user()->id)->first();
        if (!floatval($checkAccountBalance->balance) < floatval($request->amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you don;t have enough money in your account'
            ], 400);
        }

        \DB::beginTransaction();
   try {
       $newBalance = Balance::find($checkAccountBalance->id);
       $newBalance = floatval($checkAccountBalance->balance) - floatval($request->amount);
       $newBalance->save();

       $recordTransaction =  new Transaction();
       $recordTransaction->reference = Str::random(32);
       $recordTransaction->amount = floatval($request->amount);
       $recordTransaction->receiver_account = $request->to;
       $recordTransaction->initiator_account = $checkAccountBalance->account;
       $recordTransaction->save();

       \DB::commit();
           return response()->json([
               'success' => true
           ]);


   }catch(\Exception $e){
       \DB::rollback();
       return response()->json([
           'success' => false,
           'message' => 'Sorry, the transaction failed'
       ], 500);
   }

    }


  /*This function pulls out the transaction history of the logged in user's account*/
  public function transactionHistory(){

        $getAccountNumber = Balance::select('account')->where('user_id',\Auth::user()->id)->get();
        $transHistory = Transaction::select('reference', 'amount', 'receiver_account as to', 'initiator_account as from')->where('initiator_account', $getAccountNumber->acount)->where('receiver_account', $getAccountNumber->acount)->get()->toArray();
        return $transHistory;
  }


  /*This action checks the logged in users account balance*/
  public function accountBalance(){
        return Balance::select('amount')->where('user_id',\Auth::user()->id)->get()->toArray();
  }

}
