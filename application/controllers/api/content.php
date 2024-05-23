<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
//require APPPATH . '/libraries/TokenHandler.php';
use chriskacerguis\RestServer\RestController;
date_default_timezone_set('Asia/Kolkata');


class content extends RestController
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
        $this->load->model('agora_model');
        // creating object of TokenHandler class at first
        //$this->tokenHandler = new TokenHandler();
        header('Content-Type: application/json');
    }

    public function genrateToken_get()
    {
            $input = json_decode(file_get_contents('php://input'));
            $response_data = $this->Stream_model->start_stream($input);
            $this->response($response_data, RestController::HTTP_OK);
        
    }

    public function endStream_post(){
        $input = json_decode(file_get_contents('php://input'));
        $response_data = $this->Stream_model->end_stream($input);
        $this->response($response_data,RestController::HTTP_OK);
    }

    public function joinStream_post(){
        $input = json_decode(file_get_contents('php://input'));
        $response_data = $this->Stream_model->join_stream($input);
        $this->response($response_data,Restcontroller::HTTP_OK);
    }

    public function leaveStream_post(){
        $input = json_decode(file_get_contents('php://input'));
        $response_data = $this->Stream_model->leave_stream($input);
        $this->response($response_data,Restcontroller::HTTP_OK);
    }

    public function streams_get(){
        $response_data = $this->Stream_model->get_streams();
        $this->response($response_data,RestController::HTTP_OK);
    }

    public function stream_viewers_post()
    {
        $input = json_decode(file_get_contents('php://input'));
        $response_data = $this->Stream_model->get_viewers($input);
    
        $organized_data = array();
        $total_views = 0;   

        foreach ($response_data as $response) {
            $user_id = $response['user_id'];
            $total_views++;
            // Check if user_id is already in the organized_data array
            if (!isset($organized_data[$user_id])) {
                $organized_data[$user_id] = array(
                    'user_id' => $user_id,
                    'view_count' => 0, // Initialize view count

                    'viewings' => array(),

                );
            }
            $organized_data[$user_id]['view_count']++;

            // Add the formatted date and time to the viewings array
            $formatted_viewing = array(
                'start_date' => (!empty($response['start_time'])) ? date("Y-m-d", $response['start_time']) : "",
                'start_time' => (!empty($response['start_time'])) ? date("H:i:s", $response['start_time']) : "",
                'end_date' => (!empty($response['end_time'])) ? date("Y-m-d", $response['end_time']) : "",
                'end_time' => (!empty($response['end_time'])) ? date("H:i:s", $response['end_time']) : "",
            );
    
            $organized_data[$user_id]['viewings'][] = $formatted_viewing;
        }
    
        // Convert the associative array to a simple array
        $final_response = array(
            'total_views' => $total_views,
            'user_data' => array_values($organized_data),
        );
    
        $this->response($final_response, Restcontroller::HTTP_OK);
    }
}
