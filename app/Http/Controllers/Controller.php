<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    function validationHandle($validation)
    {
        foreach ($validation->getMessages() as $field_name => $messages){
            if(!isset($firstError)){

                $firstError =$messages[0];
            }
        }
        return $firstError;
    }
}
