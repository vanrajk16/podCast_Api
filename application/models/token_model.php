<?php
require APPPATH . '/libraries/TokenHandler.php';
defined('BASEPATH') OR exit('No direct script access allowed');
class token_model extends CI_Model {
	// constructor
	function __construct()
	{
		$this->tokenHandler = new TokenHandler();
		parent::__construct();
		// constructor
	
		/*cache control*/
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
	
	}

	public function token_data()
	 {
        $received_Token = $this->input->get_request_header('token', TRUE);
	    if (isset($received_Token)) {
	      try
	      {

	        $jwtData = $this->tokenHandler->DecodeToken($received_Token);
	        return $jwtData;
	      }
	      catch (Exception $e)
	      {
	        echo 'catch';
	        http_response_code('401');
	        return array( "status" => false, "message" => $e->getMessage());
	        exit;
	        
	      }
	    }else{
	      return array( "status" => false, "message" => "Invalid Token");
	    }
	  }


	  public function token_user_id()
	 {
        $received_Token = $this->input->get_request_header('token', TRUE);
	    if (isset($received_Token)) {
	      try
	      {

	        $jwtData = $this->tokenHandler->DecodeToken($received_Token);
	        return $jwtData['user_id'];
	      }
	      catch (Exception $e)
	      {
	        echo 'catch';
	        http_response_code('401');
	        return array( "status" => false, "message" => $e->getMessage());
	        exit;
	        
	      }
	    }else{
	      return array( "status" => false, "message" => "Invalid Token");
	    }
	  }
	  //check if token is on header or not.
	  public function token_check(){
        if (!empty($this->input->get_request_header('token', TRUE))){ 
			$received_Token = $this->input->get_request_header('token', TRUE);
			try
			{
  
			  $jwtData = $this->tokenHandler->DecodeToken($received_Token);
			  return true;
			}
			catch (Exception $e)
			{
			  echo 'catch';
			  http_response_code('401');
			  return false;
			  exit;
			  
			}
        } else {
			return FALSE;
		}


    }

}