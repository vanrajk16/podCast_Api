<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Kolkata');

class Api_model extends CI_Model{

    function __construct() {
        parent::__construct();

        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
    }


    	// Login mechanism
	public function login_get()
	{
		$userdata = array();
		$data = json_decode(file_get_contents('php://input'));
		
		if(!empty(file_get_contents('php://input'))){
		$credential = array('email' => $data->email, 'password' => sha1($data->password), 'status' => 0);
		$query = $this->db->get_where('users', $credential);
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$userdata['user_id'] = $row['id'];
			$userdata['name'] = $row['name'];
			$userdata['email'] = $row['email'];
			$userdata['role_id'] = $row['role_id'];
			$userdata['status'] = $row['status'];
			$userdata['code'] = 200;

			$userdata['validity'] = 1;
		} else {
			$userdata['validity'] = 0;
		}}else{
			$userdata['validity'] = -1;
		}
		return $userdata;
	}
	//registration mechnisem
	public function register_user()
	{
		$new_data = json_decode(file_get_contents('php://input'));

		$this->load->model('user_model');
		
		$response = array();
		$email_cheker = $new_data->email;
		$phone_cheker = $new_data->phone;

		$validity_email = $this->user_model->check_duplication('on_create', $email_cheker);
		$validity_phone = $this->user_model->check_duplication_phone('on_create', $phone_cheker);


		if ($validity_email != FALSE) {
			if ($validity_phone != FALSE) {
				
			
				if ($new_data->name != "") {
					$data['name'] = $new_data->name;
				} else {
					$response['status'] = 'failed';
					$response['error_reason'] = 'first_name_can_not_be_empty';
					return $response;
				}
				if ($new_data->phone != "") {
					$data['phone'] = $new_data->phone;
				} else {
					$response['status'] = 'failed';
					$response['error_reason'] = 'Phone_No_can_not_be_empty';
					return $response;
				}
				
				if ($new_data->email != "") {
					$data['email'] =$new_data->email;
				} else {
					$response['status'] = 'failed';
					$response['error_reason'] = 'email_can_not_be_empty';
					return $response;
				}
				if ($new_data->password != "") {
					$pass = $new_data->password;
					$data['password'] = sha1($pass);
				} else {
					$response['status'] = 'failed';
					$response['error_reason'] = 'Password_can_not_be_empty';
					return $response;
				}
	
				$data['role_id'] = $new_data->role_id;
				$data['status'] = $new_data->status;

				//$data['is_instructor'] = $new_data->is_instructor;
				$data['date_added'] = strtotime(date("Y-m-d H:i:s"));
				
				
				
				$create_cou = $this->create_cou($data);
				if ($create_cou) {
				$response['status'] = 'success';
				$response['error_reason'] = 'none';
				} else{
					$response['status'] = 'failed';
				$response['error_reason'] = 'insertion or stripe Cou error!';
				}
				
			}else{
					$response['status'] = 'failed';
					$response['error_reason'] = 'phone_duplication';
				}
		}else{
				$response['status'] = 'failed';
				$response['error_reason'] = 'email_duplication';
			}

			
			return $response;
	}

	private function create_cou($data){
		$this->db->insert('users', $data);
		$cou_stripe_key = $this->payment_model->create_coustm_stripe($this->db->insert_id(),$data);
		if($cou_stripe_key){
				$update_stripe = $this->update_stripe_cou_key($this->db->insert_id(),$cou_stripe_key);
				if ($update_stripe) {
						return TRUE;
					}else{
						return FALSE;
				}
			} else {
				return FALSE;
		}
		

	}

	
	private function update_stripe_cou_key($id="",$key=""){
		$data = array(
            'stripe_keys' => $key  
        );
        $query = $this->db
            ->where('id', $id)
            ->update('users', $data);
            if ($query) {
                return TRUE;
            }else {
                return FALSE;
            }
	}

}