<?php
defined('BASEPATH') or exit('No direct script access allowed');

// header("Access-Control-Allow-Origin: *");

class c_siginUp extends CI_Controller
{

	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
		parent::__construct();
		$this->load->database();
		$this->load->model('signUpMOdel/m_sigInUp');
		// header("Access-Control-Allow-Origin: *");

	}

	
	public function signUp()
	{
		$data = $this->m_sigInUp->SignUp();
		echo json_encode($data);
		return json_encode($data);
	}
	public function sigIn()
	{
		$data = $this->m_sigInUp->SigIn();
		echo json_encode($data);
		return json_encode($data);
	}
	public function resetpassordToConfirm()
	{
		$data = $this->m_sigInUp->resetpassordToConfirm();
		echo json_encode($data);
		return json_encode($data);
	}
	public function checkForTaoken()
	{
		$data = $this->m_sigInUp->checkForTaoken();
		echo json_encode($data);
		return json_encode($data);
	}
	// public function resetpassord(){
	// 	$data=$this->m_sigInUp->Resetpassord();
	// 	echo json_encode($data);
	// 	return json_encode($data);
	// }
	public function resetpassord()
	{
		$this->load->library('encrypt');
		$this->load->library('email');
		$this->load->library('form_validation');

		if ($this->input->post('username')) {

			$totaken = date("d/m/Y h:i:s");
			$username = $this->input->post('username');
			$tempuser = $this->encrypt->encode($username);
			$tempToken = $this->encrypt->encode($totaken);
			// print_r($tempuser);
			// echo "<br>";
			// print_r($this->encrypt->decode($tempuser));
			// exit();
			$data = $this->m_sigInUp->Resetpassord();
			// var_dump($data); exit;
			if ($data == true) {

				$config['mailtype'] = 'html';
				$config['charset']  = 'iso-8859-1';
				$config['wordwrap'] = TRUE;
				$this->email->initialize($config);
				$this->email->from('tayyabchohan7@gmail.com');
				$this->email->to($username);
				$this->email->subject('Reset Password');
				$this->email->message("<a href='http://localhost:3000/resetPassword/~" . $tempuser . "~/~" . $tempToken . "~' target='_blank' >Click here to Reset Password</a>");

				if ($this->email->send()) {
					echo json_encode(array(
						"create" => true,
						"message" => " Mail successfully send"
					));
					return json_encode(array(
						"create" => true,
						"message" => " Mail successfully send"
					));
				} else {
					print_r($this->email->print_debugger());
					echo json_encode(array(
						"create" => false,
						"message" => "Failed to send email"
					));
					return json_encode(array(
						"create" => false,
						"message" => "Failed to send email"
					));
				}
			} else {
				echo json_encode(array(
					"create" => false,
					"message" => "User Name Not Exist"
				));
				// return array('status' => false, 'message' => 'User Name Not Exist');
			}
		}
	}

	/********************************
	 *   end Screen item packing
	 *********************************/
}
