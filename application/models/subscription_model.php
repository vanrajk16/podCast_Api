<?php
defined('BASEPATH') or exit('No direct script access allowed');

class subscription_model extends CI_Model
{

    // constructor
	function __construct()
	{
		parent::__construct();
		/*cache control*/
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
	}

    public function createOrder($data){
        // Get Key_id And Secret_key from payment Model
        //$paymnet_info = json_decode($this->payment_model->getData());
        //get user_id from token 
        $user_id = $this->token_model->token_user_id();
         //get plan id from input json file
         $plan_id = $data->plan_id; 
         //from plan_id fetch plan detiles from database
        $plan = $this->subscription_model->getPlanById($plan_id);

        if (!$plan) {
           
            return  $this->response(['error' => 'Invalid plan ID'], RestController::HTTP_BAD_REQUEST);;
        }

        $start_date = strtotime(date('Y-m-d'));
        $end_date = strtotime(date('Y-m-d', strtotime("+$plan->duration_days days")));

        //$this->User_model->setSubscriptionStatus($user_id, 1);
        //$subscription_id = $this->SubscriptionUser_model->updateSubscription($user_id, $plan_id, $start_date, $end_date, 'inactive');
       
            $order = $this->payment_model->createPaymentOrder($user_id,$plan_id,$start_date,$end_date);
            return $order;
        
       
    }
    // Get all active subscription plans
    public function getActivePlans() {
        $query = $this->db->where('status', 'active')->get('subscription_plans');
        return $query->result();
    }

    // Get a subscription plan by ID
    public function getPlanById($plan_id) {
        $query = $this->db->where('id', $plan_id)->get('subscription_plans');
        return $query->row();
    }

    // Add a new subscription plan
    public function addPlan($data) {
        $this->db->insert('subscription_plans', $data);
        return $this->db->insert_id();
    }

    // Update an existing subscription plan
    public function updatePlan($plan_id, $data) {
        $this->db->where('id', $plan_id);
        $this->db->update('subscription_plans', $data);
    }

    // Delete a subscription plan by ID
    public function deletePlan($plan_id) {
        $this->db->where('id', $plan_id);
        $this->db->delete('subscription_plans');
    }

}
?>