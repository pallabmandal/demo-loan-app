<?php

namespace App\Http\Controllers\v1\Borrower\Loan;

use DB;
use App\Traits\FilterTrait;
use App\Traits\LoggerHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\ResponseHandler;
use App\Helpers\CustomValidator;
use App\Traits\PermissionHandler;
use App\Exceptions\PermissionException;
use App\Exceptions\DBOException;
use App\Exceptions\DataException;
use App\Http\Controllers\v1\BaseController;
use JwtAuth;
use Illuminate\Support\Str;


class LoanController extends BaseController
{
    use ResponseHandler;
    use FilterTrait;
    use PermissionHandler;
    use LoggerHelper;

    public function repay(Request $request) {
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $validator_rules = [
            'loan_id' => 'required',
            'repay_amount' => 'required'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        //Fetching and verifying the loan detials
        $loan = \App\Models\Loan::select('*')
        ->where('user_id', $user['id'])
        ->with('repayments.repayment_payments.payment')
        ->find($inputs['loan_id']);

        if(empty($loan)){
            throw new \App\Exceptions\DataException('Invalid loan passed', 400);
        }

        //Ensure that the loan is not paid and is an approved loan
        if($loan->status == config('constants.status.paid')){
            throw new DataException("This loan has been fully paid", 400);
        }
        if($loan->status != config('constants.status.approved')){
            throw new DataException("You can not repay this loan", 400);
        }

        //Get all the next repayment to ensure that the payment amount is not less than the next payment
        //and not more that the toal dues left
        $allNextRepay = \App\Models\LoanRepayment::where('loan_id', $inputs['loan_id'])
        ->where('status', '!=', config('constants.status.paid'))
        ->orderBy('week','ASC')
        ->get()->toArray();

        if(empty($allNextRepay)){
            throw new DataException("There is no due left for this loan", 400);
        }

        $getNextRepay = $allNextRepay[0];

        $totalDues = 0;

        foreach ($allNextRepay as $value) {
            $totalDues = $totalDues + $value['balance'];
        }


        if($inputs['repay_amount'] > $totalDues){
            throw new DataException("Repay amount can not be more than balance amount", 400);
        }

        if($inputs['repay_amount'] < $getNextRepay['balance']){
            throw new DataException("Repay amount is less than the balance amount", 400);
        }

        //Fully pay or partially pay one or more repayment
        try {
            
            \DB::beginTransaction();

            //Create the payment entry. This is a dummy data that resembles payment gateway (I followed the structure of stripe here)
            $repaymentDetials = new \App\Models\LoanRepaymentPayment();
            $repaymentDetials->user_id = $user['id'];
            $repaymentDetials->loan_id = $inputs['loan_id'];
            $repaymentDetials->payment_amount = $inputs['repay_amount'];
            $repaymentDetials->payment_currency = 'inr';
            $repaymentDetials->payment_id = Str::random(40);
            $repaymentDetials->payment_session_id = Str::random(40);
            $repaymentDetials->payment_status = config('constants.status.paid');
            $repaymentDetials->payment_date = \Carbon\Carbon::now();
            $repaymentDetials->status = 1;
            $repaymentDetials->save();

            $totalBalance = $inputs['repay_amount'];

            //Keep on repaying partially or fully as long as there is balance left
            while($totalBalance > 0){
                
                $nextRepay = \App\Models\LoanRepayment::where('loan_id', $inputs['loan_id'])
                ->where('status', '!=', config('constants.status.paid'))
                ->orderBy('week','ASC')
                ->first();

                if(!empty($nextRepay)){
                    $nextRepay->toArray();
                } else {
                    throw new DataException("There is no due left for this loan", 400);
                }

     

                if($totalBalance >= $nextRepay['balance']){
                    
                    $paymentAmount = $nextRepay['balance'];
                    $balanceAmount = 0.00;
                    $nextRepay->paid_on = \Carbon\Carbon::now();
                    $nextRepay->balance = 0.00;
                    $nextRepay->paid = $nextRepay->total;
                    $nextRepay->status = config('constants.status.paid');
                    $nextRepay->save();

                    $totalBalance = $totalBalance - $paymentAmount;

                    //Close the loan once all the week amount is paid.
                    if($nextRepay['week'] == $loan->loan_terms){
                        $loan->loan_close_date = \Carbon\Carbon::now();
                        $loan->status = config('constants.status.paid');
                        $loan->save();
                    }

                } else {

                    $paymentAmount = $totalBalance;
                    $balanceAmount = $nextRepay->balance - $totalBalance;;
                    $nextRepay->balance = $nextRepay->balance - $totalBalance;
                    $nextRepay->paid = $nextRepay->paid + $totalBalance;
                    $nextRepay->status = config('constants.status.partially_paid');
                    $nextRepay->save();

                    $totalBalance = 0;
                }


                //make the balance ledger entry
                $repayPaymentDetails = new \App\Models\LoanRepaymentPaymentDetials();
                $repayPaymentDetails->loan_id = $inputs['loan_id'];
                $repayPaymentDetails->loan_repayment_id = $nextRepay->id;
                $repayPaymentDetails->loan_repayment_payment_id = $repaymentDetials->id;
                $repayPaymentDetails->total = $nextRepay->total;
                $repayPaymentDetails->due = $nextRepay->balance; 
                $repayPaymentDetails->paid = $paymentAmount;
                $repayPaymentDetails->balance = $balanceAmount;
                $repayPaymentDetails->save();


            }

            \DB::commit();

            return $this->buildSuccess(true, $repaymentDetials, 'Loan payment completed successfully', Response::HTTP_OK);

        } catch (\Exception $e) {
            \DB::rollback();
            throw new DBOException($e->getMessage(), 500);
        }

    }


    public function create(Request $request)
    {
        
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $validator_rules = [
            'loan_amount' => 'required',
            'loan_terms' => 'required'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        $existing = \App\Models\Loan::where('user_id', $user['id'])->whereIn('status', [config('constants.status.active'), config('constants.status.partially_paid'), config('constants.status.pending'), config('constants.status.approved')])->count();

        if(!empty($existing)){
            throw new \App\Exceptions\DataException('You already have an existing unpaid load', 400);
        }

        $newLoan = new \App\Models\Loan();
        
        $newLoan->user_id = $user['id'];
        $newLoan->loan_amount = $inputs['loan_amount'];
        $newLoan->loan_terms = $inputs['loan_terms'];
        $newLoan->status = config('constants.status.pending');

        $newLoan->save();
        return $this->buildSuccess(true, $newLoan, 'All Loans fetched successfully', Response::HTTP_OK);
        
    }

    public function index(Request $request)
    {
        
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $query = \App\Models\Loan::where('user_id', $user['id']);

        $sortOrder = $request->input('sort_order', 'DESC');
        $sortBy    = $request->input('sort_by', 'id');
        $query = $query->orderBy($sortBy, $sortOrder);

        if ($request->has('per_page')) {
            $per_page = $request->per_page;
            $query = $query->paginate((int) $per_page);
        } 
        else {
            $query = $query->paginate($this->paginateCount);
        }
        return $this->buildSuccess(true, $query, 'All Loans fetched successfully', Response::HTTP_OK);
        
    }

    public function show(Request $request)
    {
        
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $validator_rules = [
            'id' => 'required'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        $query = \App\Models\Loan::select('*')
        ->where('user_id', $user['id'])
        ->with('repayments.repayment_payments.payment')
        ->find($inputs['id']);

        return $this->buildSuccess(true, $query, 'All Loans fetched successfully', Response::HTTP_OK);
        
    }


}
