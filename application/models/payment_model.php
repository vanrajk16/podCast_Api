<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once 'vendor/autoload.php';
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
date_default_timezone_set('Asia/Kolkata');

class Payment_model extends CI_Model
{

    // constructor
    public function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    // public function getData()
    // {
    //     $razorpay_settings = $this->db->get_where('settings', array('key' => 'stripe'))->row()->value;
    //     return $razorpay_settings;
    // }

//    public function generate_custom_receipt_number($prefix="",$user_id="",$plan_id="") {
//         // Get a unique identifier (e.g., a random number or a database-generated ID)
//         $unique_identifier = mt_rand(10000, 99999); // You can adjust this range
    
//         // Get the current timestamp (or use a specific date and time)
//         $timestamp = time();
    
//         // Format the timestamp as desired (e.g., 'YmdHis' for YearMonthDayHourMinuteSecond)
//         $formatted_timestamp = date('YmdHis', $timestamp);
    
//         // Combine the prefix, unique identifier, and formatted timestamp to create the receipt number
//         $receipt_number = $prefix . $plan_id . $formatted_timestamp . $user_id . $unique_identifier;
    
//         return $receipt_number;
//     }
    
    // Example usage:

    public function createPaymentOrder($user_id, $plan_id,$start_date,$end_date)
    {
        // Get Key_id And Secret_key from payment Model
        $payment_model = new payment_model();

        // Get Key_id And Secret_key from payment Model
        //$paymnet_info = json_decode($payment_model->getData());
    
        //from plan_id fetch plan detiles from database
        $plan = $this->subscription_model->getPlanById($plan_id);
        $user_stripe_key = $this->user_stripe_key($user_id)->stripe_keys;

        $api =  Stripe::setApiKey("sk_test_51OOwqxSEvMOSTrQ5oh6JvlBQsac2a0B0CK6Zhs5eG1tsKkWDdxpRlbcePbd5GNRzYcGu3dSsTZzscK4NAHFQisuS000QHHtN4h");


        $orderData = array(
            'customer' => $user_stripe_key,
            'amount' => $plan->price,
            'currency' => 'usd',
            'metadata' => array(
                'user_id' => $user_id,
                'plan_id' => $plan_id,
            ),
        );
        // make or create order from detiles and return order id and payment id which will be created in database
        try {

            $order = PaymentIntent::create($orderData);
            $payment_data = array(
                "order_id" => $order['id'],
                "amount" => $plan->price,
                "user_id" => $user_id,
                "plan_id" => $plan_id,
                "status" => $order['status'],

            );
            // $payment = $this->payment_model->insert_payment_detiles($payment_data);
            // Return the order ID to the Flutter app
            //$update_order_id = $this->SubscriptionUser_model->Add_order_id($user_id, $order['id']);
            $response_data = array("order_id" => $order['id'], "amount" => $plan->price);
            return $response_data;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }

    public function insert_payment_detiles($payment)
    {
        $payment_data = array(
            "order_id" => $payment['order_id'],
            "user_id" => $payment['user_id'],
            "plan_id" => $payment['plan_id'],
            "date_added" => strtotime(date("Y-m-d H:i:s")),
            "amount" => $payment['amount'],
            "status" => $payment['status'],
            "payment_type" => "stripe",
        );

        $this->db->insert('payment', $payment_data);
        return $this->db->insert_id();

    }

    public function update_payment_status($payment, $payment_status)
    {
        $payment_model = new payment_model();

        //$paymnet_info = json_decode($payment_model->getData());

        //$api = new Api($paymnet_info[0]->key_id, $paymnet_info[0]->secret_key);

      if ($payment_status =="Faliure") {

    $data_status = $this->payment_update($payment_model,$api,$payment,$payment_status);
    if ($data_status) {
        $response['status'] = "Payment Failed.";
    }
      }else{
        $expected_signature_data = $payment->razorpay_order_id . '|' . $payment->razorpay_payment_id;

        $generated_signature = hash_hmac('sha256', $expected_signature_data, $paymnet_info[0]->secret_key);

  if ($generated_signature == $payment->razorpay_signature) {
    $data_status = $this->payment_update($payment_model,$api,$payment,$payment_status);
    if($data_status)
    {
        $response['status'] = "Payment Successfull.";
        $response['transaction_id'] = $payment->razorpay_payment_id;
    }
    else{
        $response['status'] = $data_status;
    }
  }else{
    $response['status'] = "Payment Failure";
  }}

        return $response;
    }

    // Get a user's subscription history
    public function getPaymentHistory($user_id)
    {
        $query = $this->db
            ->where('user_id', $user_id)
            ->order_by('date_added', 'desc')
            ->get('payment');

            $payment_details = $query->result_array();

            foreach ($payment_details as $key => $payment_detail) {
                $payment_details[$key]['plan_name'] = $this->subscription_model->getPlanById($payment_detail['plan_id'])->plan_name;
            }
        return $payment_details;
    }

    // public function get_all_payments(){
    //     $api = new Api("rzp_test_kH5XymVnG8q2", "bEHDGdLa8Cv75SIc45L3di");
    
    //     try {
    //         // Fetch all payments
    //         $payments = $api->payment->all(null);
    //         return $payments->items;
    //     } catch (Exception $e) {
    //         log_message('error', 'Caught exception: ' . $e->getMessage());
    // show_error('An error occurred. Please try again later.', 500);
    //     }

    // }


    private function payment_update($paymentModel, $api, $paymentData, $paymentStatus) {
        try {
            $paymentInfo = json_decode($paymentModel->getData());
    
            if ($paymentStatus == "Failure") {
                $paymentUpdatedData = [
                    "status" => $paymentData->code,
                    "last_modified" => strtotime(date('Y-m-d')),
                    "error_description" => $paymentData->message,
                    "error_reason" => $paymentData->error
                ];
    
                $queryFail = $this->db
                    ->where('order_id', $paymentData->order_id)
                    ->update('payment', $paymentUpdatedData);
    
                if (!$queryFail) {
                    throw new Exception('Failed to update payment on failure');
                }
            } else {
                // Process successful payment
                $this->processSuccessfulPayment($api, $paymentData);
            }
    
            return true;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    private function processSuccessfulPayment($api, $paymentData) {
        // Retrieve payment details using Razorpay API
        $payInfo = $api->order->fetch($paymentData->razorpay_order_id)->payments();
        $payments = $payInfo->items;
    
        foreach ($payments as $payment) {
            $user_id = $payment->notes->user_id;
            $order_id = $payment->order_id;
            $transId = $payment->id;
            $status = $payment->status;
            $method = $payment->method;
            $lastModified = $payment->created_at;
            $plan_id = $payment->notes->plan_id;
        }
    
        $plan = $this->subscription_model->getPlanById($plan_id);
        $start_date = strtotime(date('Y-m-d'));
        $end_date = strtotime(date('Y-m-d', strtotime("+$plan->duration_days days")));
        // Update payment information in the database
        $paymentUpdatedData = [
            "transaction_id" => $transId,
            "status" => $status,
            "method" => $method,
            "last_modified" => $lastModified,
        ];
    
        $query = $this->db
            ->where('order_id', $order_id)
            ->update('payment', $paymentUpdatedData);
    
        if (!$query) {
            throw new Exception('Failed to update payment details');
        } else {
            $data = array(
                "user_id" => $user_id,
                "plan_id" => $plan_id,
                "status" => $status,
                "order_id" => $order_id,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "on_sub_status" => null
            );    
            if ($payment->status == "captured") {
                $data['on_sub_status'] = "Active";
                $subscription_id = $this->SubscriptionUser_model->updateSubscriptionOnPayment($data);
            } else {
                $data['on_sub_status'] = "Inactive";
                $subscription_id = $this->SubscriptionUser_model->updateSubscriptionOnPayment($data);
            }
            if (!$subscription_id) {
                throw new Exception('Failed to update subscription');
            }
        }
        return true;
    }


    public function create_coustm_stripe($user_id="",$data){

        $api =  Stripe::setApiKey("sk_test_51OOwqxSEvMOSTrQ5oh6JvlBQsac2a0B0CK6Zhs5eG1tsKkWDdxpRlbcePbd5GNRzYcGu3dSsTZzscK4NAHFQisuS000QHHtN4h");

        $customer = Customer::create([
            'email' => $data['email'],
            'name'  => $data['name'],
            'metadata' =>[
                'cid'   => $user_id
        ]
          ]);
          return $customer->id;


    }

    private function user_stripe_key($user_id=""){
        $query = $this->db->where('id', $user_id)->get('users');
        return $query->row();
    }

}
