<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
//require APPPATH . '/libraries/TokenHandler.php';


use chriskacerguis\RestServer\RestController;


class payment extends RestController
{
    protected $token;
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('subscription_model');
        $this->load->model('payment_model');
        $this->load->model('token_model');
        $this->load->model('SubscriptionUser_model');
        // creating object of TokenHandler class at first
        //$this->tokenHandler = new TokenHandler();
        header('Content-Type: application/json');
    }
    public function Onsuccess_post()
    {

        $payment_data = json_decode(file_get_contents('php://input'));
        if ($this->secure->security_key_check()) {
            //pass the status on update_payment_status() function.
            $status = "Success";

            $response_data = $this->payment_model->update_payment_status($payment_data, $status);
            $this->response($response_data, RestController::HTTP_OK);
        } else {
            return $this->set_response($this->secure->unsecure(), RestController::HTTP_UNAUTHORIZED);
        }
    }

    public function Onfaliure_post()
    {

        $payment_data = json_decode(file_get_contents('php://input'));
        if ($this->secure->security_key_check()) {
          //pass the status on update_payment_status() function. from faliure status the errors will be save on database.
            $status = "Faliure";
            $response_data = $this->payment_model->update_payment_status($payment_data, $status);
            $this->response($response_data, RestController::HTTP_OK);
        } else {
            return $this->set_response($this->secure->unsecure(), RestController::HTTP_UNAUTHORIZED);
        }
    }

    public function payment_history_post()
    {
        if ($this->secure->security_key_check()) {
            $user_id = $this->token_model->token_user_id();
            $response = $this->payment_model->getPaymentHistory($user_id);
            return $this->set_response($response, RestController::HTTP_OK);
        } else {
            return $this->set_response($this->secure->unsecure(), RestController::HTTP_UNAUTHORIZED);
        }
    }
}
