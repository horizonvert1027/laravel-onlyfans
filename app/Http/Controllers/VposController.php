<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Deposits;

class VposController extends Controller
{

    public function vpos_webhook(HttpRequest $request)
    {
        
        $request= $request->all();
        if(!empty($request)){
            $transactionID= $request['id'];
            $verifiedTxnId = Deposits::where('txn_id', $transactionID)->first();
  
            if($request["status"]=="accepted"){                
                if(!empty($verifiedTxnId)){
                    $userid= $verifiedTxnId->user_id;
                    $amount= $verifiedTxnId->amount;
                    // Add Funds to User
                    User::find($userid)->increment('wallet', $amount);

                    $verifiedTxnId->status = 'active';
                    $verifiedTxnId->save();
                }
            } else {
                if(!empty($verifiedTxnId)){
                    $verifiedTxnId->status = 'pending';
                    $verifiedTxnId->save();
                }
            }
            
        }
    }    
    
    
}    