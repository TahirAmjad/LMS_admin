<?php
class M_sigInUp extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }


  public function SignUp()
  {
    $fname = $this->input->post('FirstName');
    $LastName = $this->input->post('LastName');
    $UserName = $this->input->post('UserName');
    $password = $this->input->post('password');
    $email = $this->input->post('email');
    $Contact = $this->input->post('Contact');
    $MarketPlace = $this->input->post('MarketPlace');
    // var_dump($fname, $LastName,  $UserName, $password, $email, $Contact, $MarketPlace);
    // exit;
    $check = $this->db->query("SELECT USER_ID from lj_signup_mt where USER_ID= '$UserName'")->result_array();
    if (count($check) === 0) {

      $get_data = $this->db->query("INSERT INTO lj_signup_mt (SIGNUP_ID, USER_ID, FIRST_NAME,LAST_NAME,CONTACT_NO, EMAIL, MARKETPLACE,INSERT_DATE, PASSWORD) 
      VALUES (get_single_primary_key('lj_signup_mt','SIGNUP_ID'), '$UserName', '$fname', '$LastName',
       '$Contact', '$email', '$MarketPlace', sysdate, '$password')");
      return array('data' => $get_data, 'status' => true);
    } else {
      return array('status' => false, 'message' => 'Invalid Email');
    }
  }
  public function SigIn()
  {
    $username = $this->input->post('username');
    $password = $this->input->post('password');
    $query = $this->db->query("SELECT M.EMPLOYEE_ID,
                M.USER_NAME as USER_ID,
                M.LOCATION,
                M.E_MAIL_ADDRESS as EMAIL,
                (select MT.BUISNESS_NAME
                from emp_merchant_det DE, lz_merchant_mt MT
                WHERE DE.MERCHANT_ID = MT.MERCHANT_ID
                    AND DE.EMPLOYEE_ID = M.EMPLOYEE_ID
                    AND ROWNUM <= 1) MER_NAME,
                (select MT.MERCHANT_ID
                from emp_merchant_det DE, lz_merchant_mt MT
                WHERE DE.MERCHANT_ID = MT.MERCHANT_ID
                    AND DE.EMPLOYEE_ID = M.EMPLOYEE_ID
                    AND ROWNUM <= 1) MER_id
                FROM EMPLOYEE_MT M
                WHERE (upper(USER_NAME) = upper('$username') OR upper(E_MAIL_ADDRESS) = upper('$username') )
                AND PASS_WORD = '$password'
                AND STATUS = 1
                ")->result_array();
                
    if(count($query) > 0){
      return array('data' => $query, 'status' => true, 'message' => 'Login Successfuly');
    }else{
      $check = $this->db->query("SELECT EMAIL,USER_ID 
        FROM lj_signup_mt 
        WHERE (upper(EMAIL) = upper('$username') OR upper(USER_ID) = upper('$username') ) 
        AND PASSWORD='$password' ")->result_array();  
        if (count($check) !== 0) {

          return array('data' => $check, 'status' => true, 'message' => 'Login Successfuly');
        } else {
          return array('status' => false, 'message' => 'User Name Or Password Incorect');
        }
    }
    exit();

    // var_dump($fname, $LastName,  $UserName, $password, $email, $Contact, $MarketPlace);
    // exit;
    // $check = $this->db->query("SELECT EMAIL , PASSWORD, USER_ID from lj_signup_mt where EMAIL= '$username' and PASSWORD='$password' ")->result_array();
    
  }
  public function Resetpassord()
  {
    $username = $this->input->post('username');
    $token = $this->input->post('token');

    $check = $this->db->query("SELECT EMAIL from lj_signup_mt where EMAIL= '$username' ")->result_array();
    if ($check) {
      $this->db->query("UPDATE  lj_signup_mt  set TOKEN_DATE = sysdate , RESET_PW='$token' where EMAIL='$username'");
      return true;

      // return array('data' => $check, 'status' => true, 'message' => 'Please Check Your Email ');
    } else {
      return false;
      // return array('status' => false, 'message' => 'User Name Not Exist');
    }
  }
  public function resetpassordToConfirm()
  {
    $this->load->library('encrypt');
    $password = $this->input->post('password');
    // var_dump($password);exit;
    $emailEncrpt = $this->input->post('emailEncrpt');
    $TimeEncrpt = $this->input->post('TimeEncrpt');
    $tempTime = $this->encrypt->decode($TimeEncrpt);
    $tempemail = $this->encrypt->decode($emailEncrpt);
    $token = $this->input->post('token');
    $time_zone = $this->getTimeZoneFromIpAddress();
    date_default_timezone_set($time_zone);
    $tempDate = date("d-m-y H:i:s");
    //echo $tempDate;
    $date = $this->db->query("SELECT TO_CHAR(TOKEN_DATE,'DD-MM-YY HH24:MI:SS') TOKEN_DATE,  EMAIL from lj_signup_mt  where  EMAIL='$tempemail'")->result_array();

    $tokenDate = $date[0]['TOKEN_DATE'];
    //echo "<br>" . $tokenDate;
    $diff = (strtotime($tempDate) - strtotime($tokenDate)) / 60;
    // var_dump($diff); exit;
    $OneDay = strtotime('1 day', 0);
    //var_dump($OneDay); exit;
    if ($diff > $OneDay) {
      return array('status' => false, 'message' => 'Time Out Try Again');
    } else {
      $qry = $this->db->query("UPDATE lj_signup_mt set PASSWORD ='$password',RESET_PW='$token'  where EMAIL= '$tempemail'");
      return array('data' => $qry, 'status' => true, 'message' => 'Update Successfuly');
    }
  }
  function getTimeZoneFromIpAddress()
  {
    if ($_SERVER['HTTP_HOST'] == "localhost") {
      $clientsIpAddress = '39.45.230.102';
    } else {
      $clientsIpAddress =  $_SERVER['REMOTE_ADDR'];
    }
    $clientInformation = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $clientsIpAddress));
    $clientsLatitude = $clientInformation['geoplugin_latitude'];
    $clientsLongitude = $clientInformation['geoplugin_longitude'];
    $clientsCountryCode = $clientInformation['geoplugin_countryCode'];
    $timeZone = $this->get_nearest_timezone($clientsLatitude, $clientsLongitude, $clientsCountryCode);
    return $timeZone;
  }
  function get_nearest_timezone($cur_lat, $cur_long, $country_code = '')
  {
    $timezone_ids = ($country_code) ? DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code)
      : DateTimeZone::listIdentifiers();

    if ($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {

      $time_zone = '';
      $tz_distance = 0;

      //only one identifier?
      if (count($timezone_ids) == 1) {
        $time_zone = $timezone_ids[0];
      } else {

        foreach ($timezone_ids as $timezone_id) {
          $timezone = new DateTimeZone($timezone_id);
          $location = $timezone->getLocation();
          $tz_lat   = $location['latitude'];
          $tz_long  = $location['longitude'];

          $theta    = $cur_long - $tz_long;
          $distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat)))
            + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
          $distance = acos($distance);
          $distance = abs(rad2deg($distance));
          // echo '<br />'.$timezone_id.' '.$distance;

          if (!$time_zone || $tz_distance > $distance) {
            $time_zone   = $timezone_id;
            $tz_distance = $distance;
          }
        }
      }
      return  $time_zone;
    }
    return 'unknown';
  }

  public function checkForTaoken()
  {
    $this->load->library('encrypt');
    $userEmail = $this->input->post('emailEncrpt');
    $tempemail = $this->encrypt->decode($userEmail);
    //var_dump( $tempemail); exit;
    $qry = $this->db->query("SELECT RESET_PW from lj_signup_mt where EMAIL= '$tempemail' ")->result_array();
    print_r($qry[0]['RESET_PW']);exit;
    if ($qry[0]['RESET_PW'] != 1) {
     
      return array('status' => true, 'data' => $qry);
    }
    else{
      return array('status' => false, 'data' => $qry);
    }
  /********************************
   *   end Screen Sign Up
   *********************************/
}
}