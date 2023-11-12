<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Tp_Transaction;
use App\Models\User;
use App\Traits\PingServer;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    use PingServer;

    //top up route
    public function topup(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user_bal = $user->account_bal;
        $user_bonus = $user->bonus;
        $user_roi = $user->roi;
        $user_Ref = $user->ref_bonus; 
      
        // $response = $this->callServer('typesystem', '/top-up', [
        //     'topUpType' => $request->t_type,
        //     'userBalance' => $user_bal,
        //     'userRoi' => $user_roi,
        //     'userRef' => $user_Ref,
        //     'userBonus' => $user_bonus,
        //     'type' => $request->type,
        //     'amount' => $request->amount,
        // ]);

        if ($request->type == "Bonus") {
            if($request->t_type == "Debit"){
                $bonus_amount =  $user_bonus - $request->amount;
                $accbal_amount = $user_bal - $request->amount;
            }elseif($request->t_type == "Credit"){
                $bonus_amount =  $user_bonus + $request->amount;
                $accbal_amount = $user_bal + $request->amount;
            }
            User::where('id', $request['user_id'])
                ->update([
                    'bonus' => $bonus_amount,
                    'account_bal' => $accbal_amount,
                ]);
        } elseif ($request->type == "Profit") {
            if($request->t_type == "Debit"){
                $roi_amount =  $user_roi - $request->amount;
                $accbal_amount = $user_bal - $request->amount;
            }elseif($request->t_type == "Credit"){
                $roi_amount =  $user_roi + $request->amount;
                $accbal_amount = $user_bal + $request->amount;
            }
            User::where('id', $request->user_id)
                ->update([
                    'roi' => $roi_amount,
                    'account_bal' =>  $accbal_amount,
                ]);
        } elseif ($request->type == "Ref_Bonus") {
            if($request->t_type == "Debit"){
                $ref_amount =  $user_Ref - $request->amount;
                $accbal_amount = $user_bal - $request->amount;
            }elseif($request->t_type == "Credit"){
                $ref_amount =  $user_Ref + $request->amount;
                $accbal_amount = $user_bal + $request->amount;
            }
            User::where('id', $request->user_id)
                ->update([
                    'ref_bonus' => $ref_amount,
                    'account_bal' =>  $accbal_amount,
                ]);
        } elseif ($request->type == "balance") {
            if($request->t_type == "Debit"){               
                $accbal_amount = $user_bal - $request->amount;
            }elseif($request->t_type == "Credit"){               
                $accbal_amount = $user_bal + $request->amount;
            }
            User::where('id', $request->user_id)
                ->update([
                    'account_bal' =>  $accbal_amount
                ]);
        } elseif ($request->type == "Deposit") {
            if($request->t_type == "Credit"){
                $dp = new Deposit();
                $dp->amount = $request['amount'];
                $dp->payment_mode = 'Express Deposit';
                $dp->status = 'Processed';
                $dp->plan = $request['user_pln'];
                $dp->user = $request['user_id'];
                $dp->save();

                User::where('id', $request['user_id'])
                    ->update([
                        'account_bal' =>  $request->amount + $user_bal,
                    ]);
            }
        }

        //add history
        if($request->type != "Deposit" && $request->t_type != "Debit"){
            Tp_Transaction::create([
                'user' => $request->user_id,
                'plan' => $request->t_type,
                'amount' => $request->amount,
                'type' => $request->type,
            ]);
        }


        return redirect()->back()->with('success', 'Action Successful!');
    }
}
