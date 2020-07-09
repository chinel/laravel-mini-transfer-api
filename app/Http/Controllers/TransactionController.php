<?php

namespace App\Http\Controllers;


use App\Balance;
use App\Transaction;
use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        if (empty($checkAccount)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the account number you are trying to pay into does not exist'
            ], 400);
        }

        //check if you have enough more than or equal to the amount in your account balance
        $checkAccountBalance = Balance::select('id','balance', 'account')->where('user_id', \Auth::user()->id)->first();
        if (floatval($checkAccountBalance['balance']) < floatval($request->amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you don;t have enough money in your account'
            ], 400);
        }

        //check if this users has initiated this same transaction within the same timeframe
        $checkTransaction = Transaction::select('created_at')->where('initiator_account', $checkAccountBalance->account)->where('receiver_account', $request->to)->orderBy('id', 'DESC')->first();
       /*  return response()->json([
            'success' => $checkTransaction['created_at'],
            'message' => Carbon::now()
        ], 400);*/

        if ($checkTransaction['created_at'] === Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you are not allowed to initiate 2 transactions at the same time'
            ], 400);
        }



        \DB::beginTransaction();
   try {
       $newBalance = Balance::find($checkAccountBalance->id);
       $newBalance->balance = floatval($checkAccountBalance->balance) - floatval($request->amount);
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

/*This function allows a logged in user to fund his or her account*/
 public function fundAccount(Request $request){
     $this->validate($request, [
         'amount' => 'required',
     ]);
     $checkAccountBalance = Balance::select('id','balance', 'account')->where('user_id', \Auth::user()->id)->first();
     $newBalance = Balance::find($checkAccountBalance['id']);
     $newBalance->balance = floatval($checkAccountBalance->balance) + floatval($request->amount);
     $newBalance->save();
     return response()->json([
         'success' => true
     ]);
 }


  /*This function pulls out the transaction history of the logged in user's account*/
  public function transactionHistory(){

        $getAccountNumber = Balance::select('account')->where('user_id',\Auth::user()->id)->first();
        $transHistory = Transaction::select('reference', 'amount', 'receiver_account as to', 'initiator_account as from')->where('initiator_account', $getAccountNumber['account'])->orWhere('receiver_account', $getAccountNumber['account'])->get()->toArray();
        return $transHistory;
  }


  /*This action checks the logged in users account balance*/
  public function accountBalance(){
        return Balance::select('balance')->where('user_id',\Auth::user()->id)->get()->toArray();
  }

}
