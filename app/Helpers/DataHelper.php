<?php
namespace App\Helpers;

use Validator;
use DB;
use Illuminate\Support\Arr;

class DataHelper
{
    public static function sortDataUpdate($dbData, $idData, $key)
    {
        $dbData = json_decode(json_encode($dbData), true);
        $dbIds = Arr::pluck($dbData, $key);
        
        $insert = [];
        $persist = [];
        $delete = [];
        $restore = [];

        $insert = array_values(array_diff($idData, $dbIds));

        $exist = array_values(array_diff($idData, $insert));

        foreach ($dbData as $value) {
            if(!in_array($value[$key], $idData)){
                if(empty($value['deleted_at'])){
                    $delete[] = $value[$key];
                }
            } else {
                if(empty($value['deleted_at'])){
                    $persist[] = $value[$key];
                } else {
                    $restore[] = $value[$key];
                }
            }
        }
        $returnData = [];
        $returnData['insert'] = $insert;
        $returnData['persist'] = $persist;
        $returnData['delete'] = $delete;
        $returnData['restore'] = $restore;

        return $returnData;

    }
}
