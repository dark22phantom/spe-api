<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Transaction;

class TransactionController extends Controller{
    public function store(Request $request){
        $params = $request->json()->all();

        $validator = Validator::make($params,[
            'customer_id' => 'required|numeric|min:1',
            'transaction_amount' => 'required|numeric|min:1'
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors()
            ]);
        }

        $customerId = $params['customer_id'];
        $transactionAmount = $params['transaction_amount'];

        $discountTiers = \DB::table('discount_tiers')->get();

        $discountProbability = 0;
        $discountRate = 0;
        $wording = "Sorry, you don't get discount!";

        foreach($discountTiers as $discountTier){
            if($discountTier->min_amount <= $transactionAmount && $discountTier->max_amount >= $transactionAmount){
                $discountProbability = $discountTier->discount_probability;
                $discountRate = $discountTier->discount_rate;

                break;
            }
        }

        $probability = [
            "true" => $discountProbability,
            "false" => 100 - $discountProbability
        ];

        $result = $this->discountProbability($probability);

        if($result == "true"){
            $discountAmount = ($discountRate/100) * $transactionAmount;
            $paymentAmount = $transactionAmount - $discountAmount;
            $discountBool = 1;
            $wording = "Congratulation, you get ".$discountRate."% discount!";
        }else{
            $discountRate = 0;
            $discountAmount = 0;
            $paymentAmount = $transactionAmount;
            $discountBool = 0;
        }

        $insertTransaction = new Transaction();
        $insertTransaction->customer_id = $customerId;
        $insertTransaction->transaction_amount = $transactionAmount;
        $insertTransaction->discount_bool = $discountBool;
        $insertTransaction->discount_rate = $discountRate;
        $insertTransaction->discount_amount = $discountAmount;
        $insertTransaction->payment_amount = $paymentAmount;

        if($insertTransaction->save()){
            return response()->json([
                'status' => 200,
                'message' => "Your transaction is successful! ".$wording,
                'data' => []
            ], 200);
        }else{
            return response()->json([
                'status' => 500,
                'message' => "Server error!",
                'data' => []
            ], 500);
        }

    }

    public function discountProbability($datas) {                
		if (is_array($datas))
		{
			$max = 0;
			foreach ($datas as $key => $value) {
				$max += $value;
				$items[$key] = $max;
			}

			$random = mt_rand(1, $max);

				foreach ($datas as $item => $max)
				{
					if ($random <= $max) {
						break;
					}
				}
				 return $item;
		} else {
				throw new Exception('Parameter must be an array.');

				return false;
		}
	}
}