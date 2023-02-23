<?php
defined('BASEPATH') or exit('No direct script access allowed');

// header("Access-Control-Allow-Origin: *");

class c_auth extends CI_Controller
{

	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
		parent::__construct();
		$this->load->database();
		$this->load->model('m_user');
    }
    public function do_login()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array( "status"=> false, "message" => "Validation error"));
            return json_encode(array("status"=> false, "message" => "Validation error"));
        } else {
            $email = html_escape($this->input->post('email'));
            $password = html_escape($this->input->post('password'));
            // $password = $this->encrypt->encode($password);
            $whereConditionArray = array(
                'email' => $email,
            );
            $data = $this->m_user->get_record($whereConditionArray);
            // print_result(sha1($data->password));
            if($data){
                if(sha1($password) == $data->password){
                    echo json_encode(array( "status"=> true, "message" => "Successfullu Login", 'data' => $data));
                    return json_encode(array("status"=> true, "message" => "Successfullu Login", 'data' => $data));    
                }else{
                    echo json_encode(array( "status"=> false, "message" => "Wrong Password"));
                    return json_encode(array("status"=> false, "message" => "Wrong Password"));    
                }
            }else{
                echo json_encode(array( "status"=> false, "message" => "Email Not Exist"));
                return json_encode(array("status"=> false, "message" => "Email Not Exist"));
            }
            
        }
    }
    public function register()
    {
        $first_name = html_escape($this->input->post('first_name'));
        $last_name = html_escape($this->input->post('last_name'));
        $password = html_escape($this->input->post('password'));
        $email = html_escape($this->input->post('email'));
        $whereConditionArray = array(
            'email'=> $email
        );
        $data = $this->m_user->get_record($whereConditionArray);
        if($data){
            echo json_encode(array( "status"=> false, "message" => "Email already exist"));
            return json_encode(array("status"=> false, "message" => "Email already exist"));
        }else{
            $updateData = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => sha1($password),
                'role_id' => 2,
                'status' => 1,
                'date_added' => strtotime(date("Y-m-d H:i:s")),
                'wishlist' => json_encode(array()),
                'watch_history' => json_encode(array())
            );
            $this->m_user->insert_record($updateData);
            echo json_encode(array( "status"=> true, "message" => "Successfully register"));
            return json_encode(array("status"=> true, "message" => "Successfully register"));

        }

        
    }
    
    
}
