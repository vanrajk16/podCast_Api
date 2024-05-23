<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';



use chriskacerguis\RestServer\RestController;
/**
 * Summary of subscription
 */
class subscription extends RestController
 {
    /**
     * Summary of token
     * @var 
     */
    protected $token;
    /**
     * Summary of __construct
     */
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
    //Get All plans Which are Active 
    /**
     * Summary of plans_get
     * @return void
     */
    public function plans_get()
    {
       
        $plans = $this->subscription_model->getActivePlans();
        
        return $this->set_response($plans,RestController::HTTP_OK);
   
    }
    //Create Order And Update The user_subscription Table
    /**
     * Summary of create_order_get
     * @return void
     */
    public function create_order_post(){
       
        $user_data = json_decode(file_get_contents('php://input'));
        
                      $response_data = $this->subscription_model->createOrder($user_data);
            $this->set_response($response_data, RestController::HTTP_OK);

        
   
    
    }

    
    
    /**
     * Summary of subscription_status_get
     * @return void
     */
    public function subscription_status_post(){
       
        $user_id = $this->token_model->token_user_id();
        $response_data = $this->SubscriptionUser_model->getActiveSubscriptionStatus($user_id);
        $current_date = strtotime(date('Y-m-d'));
        $end_date = $response_data->end_date;
        $start_date = $response_data->start_date;

        if(($current_date >= $start_date && $current_date <= $end_date) && $response_data->admin_status == 0){
            $response['status'] = "TRUE";

        }else {
            $response['status'] = "FALSE";
        }
        $this->set_response($response, RestController::HTTP_OK);
  
    }


    public function active_subscription_post(){
      if ($this->secure->security_key_check())
        {
      $user_id = $this->token_model->token_user_id();
      $user_detiles = $this->SubscriptionUser_model->getActiveSubscription($user_id);
      // $user_detiles = $response_data->result_array();
     
      // var_dump($user_detiles);

      $this->set_response($user_detiles, RestController::HTTP_OK);
    }else{
        return $this->set_response($this->secure->unsecure(), RestController::HTTP_UNAUTHORIZED);
      }

    }

   
 }
?>