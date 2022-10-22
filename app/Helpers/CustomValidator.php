<?php
namespace App\Helpers;

use Validator;
use DB;

class CustomValidator extends Validator
{
    public static function validatorResult($validator, $display_message, $http_code)
    {
        $message = [];

        if($validator->fails())
        {
            foreach ($validator->errors()->all() as $key => $value)
            {
                $message[] = $value;
            }

            if(empty($display_message))
            {
                $display_message = 'Unable to validate data. Please check your input(s).';
            }

            if(empty($http_code))
            {
                $http_code = 422;
            }

            return [
                'result' => false, 
                'error' => [
                    'type' => 'validation_error',
                    'details' => $message
                ], 
                'data' => [], 
                'message' => $display_message, 
                'code' => $http_code, 
                'status_code' => $http_code
            ];
        }

        return ['code' => 200];
    }

    public static function validator($validation_input, $validation_rules, $display_message = '', $http_code = '')
    {
        $validator = Validator::make(
                        $validation_input,
                        $validation_rules
                    );

        // Call method to check the result of validation. This method can be called directly from the controllers.
        return CustomValidator::validatorResult($validator, $display_message, $http_code);
    }
}
