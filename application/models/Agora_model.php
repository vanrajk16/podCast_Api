<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/Tools-master/DynamicKey/AgoraDynamicKey/php/src/RtcTokenBuilder.php';


class Agora_model extends CI_Model{
    protected  $CHANNEL_NAME = "uncut_stream";
    protected $APP_ID = "97e90c630ed04e668e6f8afe8698c2f7";
    const APP_CERT = "0e0b7345ab9a428f80d53e37cc0bf657";

     

    function __construct() {
        parent::__construct();

    
    
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
    }

    

    public function build_token($user_id="",$user_role=""){
        $data = json_decode(file_get_contents('php://input'));
         $stream['app_id'] = "13f32762df5f44b9a7b4c59eedde58eb";
         $appCertificate = "b20b88a99278471698423b4e86cecf93";
        //$stream['app_id'] = $CHANNEL_NAME;
        //$appCertificate = $APP_ID;
        $stream['ch_name'] = self::APP_CERT;
        $stream['uid'] = strval($user_id);
        $role = ($user_role == "Admin") ? 1 : 2;
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now"))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        

        $stream['token'] = RtcTokenBuilder::buildTokenWithUserAccount($stream['app_id'], $appCertificate, $stream['ch_name'], $stream['uid'], $role, $privilegeExpiredTs);

        $update_db = $this->add_to_tb($stream);
        if($update_db['status'] == TRUE){
            $stream['stream_id'] = $update_db['id'];
            return $stream;
        } else{
            return ['status'=>'Failed','error'=>'Insertion Problem'];
        }
    }

    public function build_token_user($user_id,$user_role){
        $data = json_decode(file_get_contents('php://input'));
        // $stream['app_id'] = "13f32762df5f44b9a7b4c59eedde58eb";
        // $appCertificate = "b20b88a99278471698423b4e86cecf93";
        $stream['app_id'] = "97e90c630ed04e668e6f8afe8698c2f7";
        $appCertificate = "0e0b7345ab9a428f80d53e37cc0bf657";
        $stream['ch_name'] = "uncut_stream";
        $stream['uid'] = strval($user_id);
        $role = ($user_role == "user") ? 2 : ['Error' => 'can`t identify user role'];
        $expireTimeInSeconds = 3600;
        $currentTimestamp = (new DateTime("now"))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $stream['token'] = RtcTokenBuilder::buildTokenWithUserAccount($stream['app_id'], $appCertificate, $stream['ch_name'], $stream['uid'], $role, $privilegeExpiredTs);

        // $update_db = $this->add_to_tb($stream);
        // if($update_db['status'] == TRUE){
        //     $stream['stream_id'] = $update_db['id'];
            return ['status' => true,'res' => $stream];
        // } else{
        //     return ['status'=>'Failed','error'=>'Insertion Problem'];
        // }
    }

    private function add_to_tb($data){
        $data['start_time'] = strtotime(date("Y-m-d H:i:s"));
     $que =   $this->db->insert('live_stream', $data);
        if($que)
        {
            $res['status'] = TRUE;
            $res['id'] = $this->db->insert_id();
        } else 
            {
                $res['status'] = FALSE;
                $res['id'] = null;
            }
        return $res;

    }


}

