<?php

namespace App\Http\Controllers\v1;

use Validator;
use App\Traits\FilterTrait;
use Illuminate\Support\Arr;
use App\Traits\LoggerHelper;
use Illuminate\Http\Request;
use App\Traits\CustomValidator;
use App\Traits\ResponseHandler;
use Request as RequestSingleton;
use App\Traits\PermissionHandler;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\PermissionException;
use App\Exceptions\InvalidFilterJsonException;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    use ResponseHandler;
    use FilterTrait;
    use PermissionHandler;
    use LoggerHelper;

    protected $relationSelectable = [];
    protected $allowedSelectables = [];
    protected $validator = [];
    protected $updatable = [];
    protected $allowedWiths = [];
    protected $allowedLogs = [];
    protected $paginateCount = 15;
    protected $indexValidation  = "";
    protected $createValidation = "";
    protected $updateValidation = "";
    protected $deleteValidation = "";

    protected function addTablePrefix($data, $prefix){
        if(empty($prefix)){
            return $data;
        }
        
        for ($i=0; $i < sizeof($data); $i++) { 
            $data[$i] = $prefix.'.'.$data[$i];
        }
        return $data;
    }

    protected function parseSelectable($query)
    {
        $data = [];

        if(!empty($query)){
            $data = explode(',', $query);
        }
  
        $data = array_intersect($data, $this->allowedSelectables);

        foreach($this->strictSelectables as $val) {
            if(!in_array($val, $data)) {
                array_push($data,$val);
            }
        }
        return $data;
    }

    protected function getUserId() 
    {
        $user = Auth::user();
        $primaryUserId = $user->id;
        if(!empty($user->primary_user_id))
            $primaryUserId = $user->primary_user_id;

        return $primaryUserId;
    }

    protected function addWheres($query) 
    {   
        $filters = json_decode(RequestSingleton::input('filters', '{}'), true);
        
        if(json_last_error() != JSON_ERROR_NONE) 
        {
            throw new InvalidFilterJsonException();
        }

        return $this->filterQuery($filters, $query);
    }

    protected function getWiths() 
    {
        $withsFromRequest = explode(',', RequestSingleton::input('attributes', ''));

        $withs = array_intersect($withsFromRequest, $this->allowedWiths);

        for ($i=0; $i < sizeof($withs); $i++) { 

            $relation = str_replace('.', '>', $withs[$i]);
            
            $withData = explode(',', RequestSingleton::input('attributesdata:'.$relation, ''));
      
            if(!empty($this->relationSelectable[$withs[$i]])){
                $withData = array_intersect($withData, $this->relationSelectable[$withs[$i]]);
            }
            //check if relation foreign key exist or not
            if(!empty($withData)){
                if(!empty($this->strictRelationSelectables[$withs[$i]])) {
                    foreach($this->strictRelationSelectables[$withs[$i]] as $val) {
                        if(!in_array($val, $withData)) {
                            array_push($withData,$val);
                        }
                    }
                }
            }
            if(!empty($withData)){
                $withs[$i] = $withs[$i].':'.implode(',', $withData);
            }
        }
        return $withs;
    }

    protected function setPaginateSize()
    {
        $pageSize = RequestSingleton::input('per_page', '');
        if (!empty($pageSize)) {
            $this->paginateCount = $pageSize;
        }
    }

    protected function deleteValodator() {
        return $this->validator;
    }


    protected function storeValidator() {
        return $this->validator;
    }

    protected function updateValidator($id) {
        return $this->validator;
    }

    protected function getSearchColumns()
    {
        return $data;
    }

    /* Methods to get and check permissions */
    protected function indexPermission($permission) {
        return true;
    }

    protected function showPermission($permission) {
        return true;
    }

    protected function createPermission($permission) {
        return true;
    }

    protected function updatePermission($permission) {
        return true;
    }

    protected function destroyPermission($permission) {
        return true;
    }

    protected function searchValidation($permission) {
        return true;
    } 
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) 
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request)
     {
        //
     }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
    }

    /**
     * @param array $dataToLog
     */
    protected function log($requestType,$dataToLog){
      //array_intesect(substr(strrchr(get_class($this), "\\"), 1),$this->allowedLogs)
        $dataToLog['user_id'] = $this->getUserId();
        return \Log::info($requestType.'- ' . substr(strrchr(get_class($this), "\\"), 1), $dataToLog);
    }

    //Method to search all the columns in the model
    protected function searchAll(Request $request)
    {   
        if(!$this->searchValidation($request)){
            throw new PermissionException();
        }

        $columns = $this->getSearchColumns();
        $keyword = $request->search;
     
        $sortOrder = $request->input('sort_order', 'ASC');
        $sortBy = $request->input('sort_by', 'id');

        
        $query = $this->model->query();
        
        $query = $query->with($this->getWiths());

        foreach($columns as $column){
            $query->orWhere($column, 'LIKE', '%'.$keyword.'%');
            
            $query = $this->addWheres($query);
        }
        
        $data = $query->orderBy($sortBy, $sortOrder);

        if($request->has('per_page')){
            $per_page = $request->per_page;
            $data = $query->paginate((int)$per_page);
        }
        else{
            $data = $query->paginate($this->paginateCount);
        }

        $data->data = $this->getIndexResponse($data);

        return $this->buildSuccess(true, $data, 'Data loaded successfully', Response::HTTP_OK);   
    }

}