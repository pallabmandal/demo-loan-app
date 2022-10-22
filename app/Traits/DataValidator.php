<?php 
namespace App\Traits;

use Validator;
use DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Carbon\Carbon;

trait DataValidator{

    public function checkAlphaNumeric($data)
    {
        if(preg_match('/[^a-z_\-0-9]/i', $data))
        {
            return false;
        } else{
            return true;
        }
    }    
}