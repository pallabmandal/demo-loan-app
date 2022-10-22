<?php

namespace App\Http\Controllers\v1\Admin\Loan;

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

class LoanController extends BaseController
{
    use ResponseHandler;
    use FilterTrait;
    use PermissionHandler;
    use LoggerHelper;

    public function updateLoanStatus(Request $request)
    {
        
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $validator_rules = [
            'loan_id' => 'required',
            'status' => 'required'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        $existing = \App\Models\Loan::find($inputs['loan_id']);

        if(empty($existing)){
            throw new \App\Exceptions\DBOException('Unable to find loan', 400);
        }
        if($existing->status != config('constants.status.pending')){
            throw new \App\Exceptions\DBOException('You can not work on this loan', 400);
        }
        \DB::beginTransaction();
        try {
            if($inputs['status'] == config('constants.status.rejected')){
                $existing->status = config('constants.status.rejected');
                $existing->save();
            } else if ($inputs['status'] == config('constants.status.approved')){
                $existing->status = config('constants.status.approved');
                $existing->loan_start_date = \Carbon\Carbon::now();
                $existing->approved_by = $user['id'];
                $existing->save();

                $perWeekAmount = round(($existing->loan_amount / $existing->loan_terms), 2);
                $lastWeek = round(($existing->loan_amount - $perWeekAmount*($existing->loan_terms-1)), 2);
                
                $loanTerms = [];
                $week = 1;
                while($week <= $existing->loan_terms){
                    $perTerm = [];
                    $perTerm['user_id'] = $existing->user_id;
                    $perTerm['loan_id'] = $existing->id;
                    $perTerm['week'] = $week;
                    if($week < $existing->loan_terms){
                        $perTerm['repay_amount'] = $perWeekAmount;
                    } else {
                        $perTerm['repay_amount'] = $lastWeek;
                    }
                    $perTerm['repay_date'] = \Carbon\Carbon::now()->addDays($week*7);
                    $perTerm['total'] = $perTerm['repay_amount'];
                    $perTerm['paid'] = 0.00;
                    $perTerm['balance'] = $perTerm['repay_amount'];
                    $perTerm['status'] = config('constants.status.pending');
                    $perTerm['created_at'] = \Carbon\Carbon::now();
                    $perTerm['updated_at'] = \Carbon\Carbon::now();
                    $loanTerms[] = $perTerm;
                    $week++;
                }

                $allTerms = \App\Models\LoanRepayment::insert($loanTerms);
            }

        \DB::commit();            
        } catch (\Exception $e) {
            \DB::rollback();
            throw new \App\Exceptions\DBOException($e->getMessage(), 500);
        }

        return $this->buildSuccess(true, $existing, 'Loans Updated successfully', Response::HTTP_OK);
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
        ->with('repayments.repayment_payments.payment', 'user')
        ->find($inputs['id']);

        return $this->buildSuccess(true, $query, 'All Loans fetched successfully', Response::HTTP_OK);
        
    }

    public function index(Request $request)
    {
        
        $user = \JwtAuth::getAuth();
        $inputs = $request->all();

        $query = \App\Models\Loan::with('user');

        if(!empty($inputs['user_id'])){
            $query = $query->where('user_id', $inputs['user_id']);
        }

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


}
