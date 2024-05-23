<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Stream_model extends CI_Model{
     
    function __construct() {
        parent::__construct();
        
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
    }

    

    public function start_stream(){
        $user_id = $this->token_model->token_user_id();
        $user_role = $this->User_model->get_user_role($user_id);
        if ($user_role == "Admin") {
            $genrate_token = $this->agora_model->build_token($user_id,$user_role);
        } else {
           return ['Error' => 'user not allowed to genrate stream'];
        }
        
        return $genrate_token;
    }

    public function end_stream($data){
        $user_id = $this->token_model->token_user_id();
        if($user_id == $data->uid){
        $new_data['end_time'] = strtotime(date("Y-m-d H:i:s"));

        $update = $this->db->where('uid',$data->uid)
                            ->where('id',$data->stream_id)
                            ->update('live_stream',$new_data);

                            if($update){
                                $res = ['status' => TRUE]; 
                            }else{
                                $res = ['status' => false]; 

                            }
                            } else{
                                $res = ['status' => false,'Error' => 'Aceess Denied!!','Reason' => 'User not allowed to END stream!'];
                            }
                            return $res;

    }

   public function join_stream($data){
    
    $user_id = $this->token_model->token_user_id();
    $genrate_token = $this->agora_model->build_token_user($user_id,"user");
    if ($genrate_token['status'] ==TRUE) {
        $add_data['user_id'] = $user_id;
        $add_data['start_time'] = strtotime(date("Y-m-d H:i:s"));
        $add_data['stream_id'] = $data->stream_id;
        $insert = $this->db->insert('stream_activity',$add_data);
        $new_id = $this->db->insert_id();
        if ($insert) {
            $res = ["status" => "success", "join_id" => $new_id, "token" => $genrate_token['res']['token'], "app_id" => $genrate_token['res']['app_id'], "ch_name" =>  $genrate_token['res']['ch_name']];
        } else {
            $res = ["status" => "Failed"];
        }
    }
   
    return $res;
   }

   public function leave_stream($data)
   {
       try {
           // Your existing code
           $update_data['end_time'] = strtotime(date("Y-m-d H:i:s"));
           $join_id = $data->join_id ?? null    ;
           $user_id = $this->token_model->token_user_id();
           
           if ($join_id === null) {
            throw new Exception("join_id is not set.");
        }
        

           $update_leave = $this->db->where('id', $join_id)
                                   ->where('user_id', $user_id)
                                   ->update('stream_activity', $update_data);
   

           if ($update_leave) {
               $res = ['status' => 'success'];
           } else {
               $res = ['status' => 'failed'];
           }
   
           return $res;
       } catch (Exception $e) {
           // Log or handle the exception
           $error_message = $e->getMessage();
           $res = ['status' => 'error', 'message' => $error_message];
           return $res;
       }
   }

   public function get_viewers($data){
    $stream_id = $data->stream_id;
    $qury = $this->db->where('stream_id',$stream_id)
                     ->get('stream_activity');
    if ($qury) {
        return $qury->result_array();
    } else {
        return ['status' => 'Failed on retriving'];
    }
   }

   public function get_streams(){
    $streams = $this->db->get('live_stream');
    return $streams->result_array();
   }
}

