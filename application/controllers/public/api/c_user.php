<?php
defined('BASEPATH') or exit('No direct script access allowed');

// header("Access-Control-Allow-Origin: *");

class c_user extends CI_Controller
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
    public function get_profile_info()
    {
        $user_id = $this->input->post('user_id');
        $user_email = $this->input->post('user_email');
        $whereConditionArray =  array(
            'id' =>$user_id,
            'email' => $user_email
        );
        $data = $this->m_user->get_record($whereConditionArray);
        if(file_exists(FCPATH. 'uploads/user_image/' .$data->user_image)){
            $data->imageFound = "yes";
         }else{
            $data->imageFound = 'no';
         }
         
		echo json_encode($data);
		return json_encode($data);
    }

    public function save_image(){
        $this->load->library('upload');
       
        $_FILES['file']['name'] = $_FILES['file']['name'];
            $__FILES['file']['type'] = $_FILES['file']['type'];
            $__FILES['file']['tmp_name'] = $_FILES['file']['tmp_name'];
            $__FILES['file']['error'] = $_FILES['file']['error'];
            $__FILES['file']['size'] = $_FILES['file']['size'];
            $this->upload->initialize($this->set_upload_options());
            if (!$this->upload->do_upload('file')) {
                
            } else {
                echo json_encode(array(
                    'upload' => true
                ));
                return json_encode(array(
                    'upload' => true
                ));
            }
        
    }
    private function set_upload_options()
    {
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');
        $config = array();
        $config['upload_path'] = './assets/upload/users/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
     // $config['max_size'] = '20000';
        $config['overwrite'] = true;
        return $config;
    }
    public function save_profile_info()
    {
        $id = html_escape($this->input->post('id'));
        $tabValue = html_escape($this->input->post('tabValue'));
        $whereConditionArray = array('id'=> $id);
        $data = $this->m_user->get_record($whereConditionArray);
        if($tabValue == 1){
            $first_name = html_escape($this->input->post('first_name'));
            $last_name = html_escape($this->input->post('last_name'));
            $biography = html_escape($this->input->post('biography'));
            $updateData = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'biography' => $biography
            );
        }elseif($tabValue == 2){
            $password = html_escape($this->input->post('password'));
            $new_password = html_escape($this->input->post('new_password'));
            $confirm_password = html_escape($this->input->post('confirm_password'));
            
            if(sha1($password) != $data->password){
                echo json_encode(array( "update"=> false, "message" => "Old password doesn't match"));
                return json_encode(array("update"=> false, "message" => "Old password doesn't match"));    
            }else{
                $updateData = array(
                    'password' => sha1($confirm_password),
                ); 
            }
        }else{
            $temp =  rand(10,10000);
            $user_photo = 'photo_' . $id ."_". $temp .".jpg";
            if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != "") {
                move_uploaded_file($_FILES['user_image']['tmp_name'], 'uploads/user_image/'.$user_photo);
                if(file_exists(FCPATH. 'uploads/user_image/' .$user_photo)){
                    $data->imageFound = "yes";
                 }else{
                    $data->imageFound = 'no';
                 }
                 $data->user_image = $user_photo;
                $updateData = array(
                    'user_image' => $user_photo
                ); 
            }else{
                echo json_encode(array( "update"=> false, "message" => "Failed to update profile photo"));
                return json_encode(array("update"=> false, "message" => "Failed to update profile photo"));    
            }

            // if(!$this->m_user->upload_user_image($id)){
                
            // }else{
                
            //     echo json_encode(array( "update"=> true, "message" => "Successfully updated profile photo" ,'data' =>$data));
            //     return json_encode(array("update"=> true, "message" => "Successfully updated profile photo" , 'data' => $data));      
            // }
        }
        
        if($this->m_user->update_record($whereConditionArray,$updateData)){
            if($tabValue == 1){
                $message = "Successfully updated profile info";
            }elseif($tabValue ==  2){
                $message = "Successfully updated password";
            }else{
                $message = "Successfully updated profile photo";
            }

            echo json_encode(array( "update"=> true, "message" => $message ,'data' =>$data));
            return json_encode(array("update"=> true, "message" => $message , 'data' => $data));    

        }else{
            if($tabValue == 1){
                $message = "Failed to update profile info";
            }elseif($tabValue ==  2){
                $message = "Failed to update password";
            }else{
                $message = "Failed to update profile photo";
            }
            echo json_encode(array( "update"=> false, "message" => $message));
            return json_encode(array("update"=> false, "message" => $message));    
        }
		
    }
    
}
