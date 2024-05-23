<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SubscriptionUser_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Create a new subscription record for a user
    public function createSubscription($user_id, $plan_id, $start_date, $end_date, $status) {
        $data = array(
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status
            
        );

        $this->db->insert('users_subscription', $data);
        return $this->db->insert_id();
    }

       // Update a subscription over Payment Order Create record for a user
       public function updateSubscription($user_id, $plan_id, $start_date, $end_date, $status) {
        $data = array(
            'plan_id' => $plan_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status,
        );
        $query = $this->db

            ->where('user_id', $user_id)
            ->update('users_subscription', $data);
            if ($query) {
                return TRUE;
            }else {
                return FALSE;
            }

            $this->db->insert('subscription_history', $data);
            
    }

    public function Add_order_id($user_id,$order_id) {
        $data = array(
            'order_id' => $order_id,
           
        );
        $query = $this->db

            ->where('user_id', $user_id)
            ->update('users_subscription', $data);
            if ($query) {
                return TRUE;
            }else {
                return FALSE;
            }
    }
    // Get a user's active subscription
    // public function getActiveSubscription($user_id) {
    //     $current_date = date('Y-m-d');

    //     $query = $this->db
    //         ->where('user_id', $user_id)
    //         ->where('start_date <=', $current_date)
    //         ->where('end_date >=', $current_date)
    //         ->get('users_subscription');

    //     return $query->row();
    // }


    public function getActiveSubscription($user_id) {

        $query = $this->db
            ->where('user_id', $user_id)
            
            ->get('users_subscription');
        $resps = $query->row_array();

      
            $resps['plan_name'] = $this->subscription_model->getPlanById($resps['plan_id'])->plan_name;
            $resps['remaining_days'] = $this->calculate_rem_time($user_id);
        
      
        return $resps;
    }

    public function getActiveSubscriptionStatus($user_id) {

        $query = $this->db
            ->where('user_id', $user_id)
            
            ->get('users_subscription');
       
      
        return $query->row();
    }

    
    // Get a user's subscripti_on history
    public function getSubscriptionHistory($user_id) {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('date_added', 'desc')
            ->get('payment');

        return $query->result();
    }
    //Update User Subscrption Detiles.
    public function updateSubscriptionOnPayment($data) {
        if ($data['on_sub_status']=="Active") {
                $temp_sub_id = $this->addSubscriptionHistory($data);
                $data_this = array(
                    'plan_id' => $data['plan_id'],
                    'order_id' => $data['order_id'],
                    'sub_his_id' => $temp_sub_id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => $data['on_sub_status']
                );     
             } else {
                $temp_sub_id = $this->addSubscriptionHistory($data);
        $data_this = array(
            'plan_id' => $data['plan_id'],
            'status' => $data['on_sub_status'],
          
        );
             }
        
        $query = $this->db
            ->where('user_id', $data['user_id'])
            ->update('users_subscription', $data_this);

        if ($query) {
            return TRUE;
        }else {
            return FALSE;
        }
    }
    public function addSubscriptionHistory($data){
        $data_this = array(
            'user_id' => $data['user_id'],
            'plan_id' => $data['plan_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'order_id' => $data['order_id']
            
        );

        $this->db->insert('subscription_history', $data_this);
        return $this->db->insert_id();
    }
    public function updateSubscriptionHistory($user_id,$order_id,$plan_id,$status)
    {
        $data = array(
            'plan_id' => $plan_id,
            
            'status' => $status
        );
        $query = $this->db
            ->where('user_id', $user_id)
            ->where('order_id',$order_id)
            ->update('subscription_history', $data);

        if ($query) {
            return TRUE;
        }else {
            return FALSE;
        }
    }

    public function user_subscription_status(){
        $user_id = $this->token_model->token_user_id();
        $response_data = $this->SubscriptionUser_model->getActiveSubscription($user_id);
        $current_date = strtotime(date('Y-m-d'));
        $end_date = $response_data->end_date;
        $start_date = $response_data->start_date;

        if(($current_date >= $start_date && $current_date <= $end_date) && $response_data->admin_status == 0 && $response_data->status == "active"){
            $response = TRUE;

        }else {
            $response = FALSE;
        }
        return $response;
    
    }


    
   private function calculate_rem_time($user_id){
    $query = $this->db
    ->where('user_id', $user_id)
   
    ->get('users_subscription');
    $res = $query->result_array();
    $start_date = strtotime(date('Y-m-d'));
    foreach($res as $key => $re){
    $end_date = $re['end_date'];
    }
    $datediff = $end_date - $start_date;

    $data = round($datediff / (60 * 60 * 24));

    return $data;
   }
 
}
?>