<?php

defined('BASEPATH') or exit('No direct scripet allow to access');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/TokenHandler.php';


use chriskacerguis\RestServer\RestController;

class authentication extends RestController{

    protected $token;
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->tokenHandler = new TokenHandler();
        header('Content-Type: application/json');

    }

    public function login_post(){
        $userdata = $this->Api_model->login_get();
        if ($userdata['validity']==1) {
            $userdata['token'] = $this->tokenHandler->GenerateToken($userdata);
        }
        return $this->set_response($userdata,RestController::HTTP_OK);
    }

    public function register_post(){
        $userdata = $this->Api_model->register_user();
        return $this->set_response($userdata,RestController::HTTP_OK);
    }
}