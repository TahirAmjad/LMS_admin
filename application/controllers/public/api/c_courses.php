<?php
defined('BASEPATH') or exit('No direct script access allowed');

// header("Access-Control-Allow-Origin: *");
require_once(APPPATH.'libraries/2checkout/lib/Twocheckout.php');
Twocheckout::privateKey('BA780B6F-FCA8-484A-8912-AE25BC027A04'); //Private Key
Twocheckout::sellerId('901416345'); // 2Checkout Account Number
Twocheckout::sandbox(true); // Set to false for production accounts.
Twocheckout::verifySSL(false);

class c_courses extends CI_Controller
{

	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
		parent::__construct();
		$this->load->database();
        $this->load->library('form_validation');
		$this->load->model('m_courses');
		// $this->load->model('m_categories');
		$this->load->model('m_section');
        $this->load->model('m_lesson');
    }
    // courses
    public function get_courses()
    {
        $data = $this->crud_model->get_latest_10_course();
		foreach ($data as $key => $value) {
            $data[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
            $data[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
                $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
                $average_ceil_rating = 0;
            }
            $data[$key]['average_ceil_rating'] = $average_ceil_rating;

        }
        echo json_encode($data);
		return json_encode($data);
    }
    public function get_top_courses()
    {

        $data = $this->crud_model->get_top_courses()->result_array();
        foreach ($data as $key => $value) {
            $data[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
            $data[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
                $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
                $average_ceil_rating = 0;
            }
            $data[$key]['average_ceil_rating'] = $average_ceil_rating;
        }
        
        // print_result($data);
        echo json_encode($data);
        return json_encode($data);
    }
    public function get_load_more_course()
    {
        $start= $this->input->post('start');
        $limit= $this->input->post('limit');
        $data = $this->crud_model->get_load_more_course($limit,$start);
        foreach ($data as $key => $value) {
            $data[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
            $data[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
                $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
                $average_ceil_rating = 0;
            }
            $data[$key]['average_ceil_rating'] = $average_ceil_rating;
        }
        
        // print_result($data);
        echo json_encode($data);
        return json_encode($data);
    }

    public function course_details(){
        $course_slug =  $this->input->post('courseSlug');
        $user_id =  $this->input->post('user_id');
        $whereConditionArray=  array(
            'course_slug' => $course_slug,
            'status' => 'active'
        );
        $data = $this->m_courses->get_record($whereConditionArray);
        $starBar =  array();
        for($i = 1; $i <= 5; $i++){
            array_push($starBar ,$this->crud_model->get_percentage_of_specific_rating($i, 'course', $data->id));
            
        }
        
        // print_result($starBar);
        if($data){


            $course_details = $this->crud_model->get_course_by_id($data->id)->row_array();
            $instructor_details = $this->user_model->get_all_user($data->user_id)->row_array();
            
            $total_rating =  $this->crud_model->get_ratings('course', $course_details['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $course_details['id'])->num_rows();
            if ($number_of_ratings > 0) {
              $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
              $average_ceil_rating = 0;
            }
            $course_details['average_ceil_rating'] = $average_ceil_rating;
            $course_details['courseImage'] = $this->crud_model->get_course_thumbnail_url($data->id);
            
            $enrolled_history = $this->db->get_where('enrol' , array('user_id' => $user_id,'course_id' => $data->id))->num_rows();
            if ($enrolled_history > 0) {
                $course_details['is_purchased'] = true;
            }else {
                $course_details['is_purchased'] =  false;
            }

            
            $whereConditionArray = array(
                'course_id'=> $data->id
                );
            $lessons = $this->m_lesson->get_records($whereConditionArray);
            $sectionsTemp = array();
            if($lessons){
                foreach ($lessons as $key => $value) {
                   $whereConditionArray = array(
                    'id'=> $value->section_id
                    );
                   $section = $this->m_section->get_record($whereConditionArray);
                    array_push($sectionsTemp, $section);
                }
            }
            $new_array = array_map("unserialize", array_unique(array_map("serialize", $sectionsTemp)));
            $sections = array_values($new_array);
            
            $ratings = $this->crud_model->get_ratings('course', $data->id)->result_array();
            foreach ($ratings as $key => $value) {
               $ratings[$key]['userImage'] = $this->user_model->get_user_image_url($value['user_id']);
               $user_details = $this->user_model->get_user($value['user_id'])->row_array();
               $ratings[$key]['userName']  = $user_details['first_name'].' '.$user_details['last_name'];
               $ratings[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            }
            $other_realted_courses = $this->crud_model->get_courses($course_details['category_id'], $course_details['sub_category_id'])->result_array();
            echo json_encode(array('data' =>$course_details , 'lessons'=> $lessons, 'sections'=> $sections,'ratings'=> $ratings ,'instructor_details' =>$instructor_details,'other_realted_courses' => $other_realted_courses, 'starBar' => $starBar));
    		return json_encode(array('data' =>$course_details , 'lessons'=> $lessons, 'sections'=> $sections,'ratings'=> $ratings ,'instructor_details' =>$instructor_details, 'other_realted_courses' => $other_realted_courses, 'starBar' => $starBar));
        }else{
            echo json_encode(array('data' =>false , 'lessons'=> false));
            return json_encode(array('data' =>false , 'lessons'=> false));
        }
    }
    public function my_courses()
    {
        $user_id = $this->input->post('user_id');
        $my_courses = $this->user_model->my_courses_react($user_id)->result_array();
        $categories = array();
        $my_courses_details =  array();
        foreach ($my_courses as $key => $my_course) {
            $course_details = $this->crud_model->get_course_by_id($my_course['course_id'])->row_array();
            array_push($my_courses_details, $course_details);
            // $instructor_details = $this->user_model->get_all_user($course_details['user_id'])->row_array();
        }
        foreach ($my_courses_details as $key => $value) {
         $instructor_details = $this->user_model->get_all_user($value['user_id'])->row_array();
         $my_courses_details[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
         $my_courses_details[$key]['instructor'] = $instructor_details['first_name'] . " " . $instructor_details['last_name'];
         
         $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
              $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
              $average_ceil_rating = 0;
            }
            $my_courses_details[$key]['average_ceil_rating'] = $average_ceil_rating;

        }
        echo json_encode(array('my_courses' =>$my_courses_details , 'categories'=> $categories));
        return json_encode(array('my_courses' =>$my_courses_details , 'categories'=> $categories));

    }
    public function enrol_student_react()
    {
        $course_id = $this->input->post('course_id');
        $user_id = $this->input->post('user_id');
        $token = $this->input->post('token');

        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $address = $this->input->post('address');
        $city = $this->input->post('city');
        $state = $this->input->post('state');
        $zipCode = $this->input->post('zipCode');
        $country = $this->input->post('country');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $course_price = $this->input->post('course_price');
        if($this->db->get_where('enrol', array('course_id' => $course_id,'user_id' =>$user_id ))->num_rows() == 0){
            $user_id =  $user_id;
           
            $charge = Twocheckout_Charge::auth(array(
                "merchantOrderId" => "123",
                "token"      => $token,
                "currency"   => 'USD',
                "total"      => $course_price,
                "billingAddr" => array(
                    "name" => $first_name ." " .$last_name,
                    "addrLine1" => $address,
                    "city" => $city,
                    "state" => $state,
                    "zipCode" => $zipCode,
                    "country" => $country,
                    "email" => $email,
                    "phoneNumber" => $phone
                )
            ));
            if ($charge['response']['responseCode'] == 'APPROVED') {
                $this->crud_model->enrol_student_react($user_id);
                $amount_paid = $charge['response']['total'];
                $method = "Card Payment";
                $this->crud_model->course_purchase_resct($user_id, $method, $amount_paid,$course_id);    
                echo json_encode(array('enroll' =>true ));
            return json_encode(array('enroll' =>true));
            }else{
                echo json_encode(array('enroll' =>false ));
            return json_encode(array('enroll' =>false));
            }
            

            
            
            
        }else{
            
            exit();
            echo json_encode(array('enroll' =>false ));
            return json_encode(array('enroll' =>false));
        }
    }
    public function rating_and_reviews()
    {
        $data['review'] = $this->input->post('review_of_a_course');
        $data['ratable_id'] = $this->input->post('course_id');
        $data['ratable_type'] = 'course';
        $data['rating'] = $this->input->post('rating_star');
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['user_id'] = $this->input->post('user_id');
        $this->crud_model->rate($data);
                echo json_encode(array('rating' =>true ));
                return json_encode(array('rating' =>true));
    }
    public function get_menu_categories()
    {
       $categories = $this->crud_model->get_categories()->result_array();
       $sub_categories = array();
       foreach ($categories as $key => $value) {
           $result = $this->crud_model->get_sub_categories($value['id']);
           array_push($sub_categories, $result);
       }
        echo json_encode(array('categories' =>$categories,'sub_categories' => $sub_categories ));
        return json_encode(array('categories' =>$categories,'sub_categories' => $sub_categories ));
       
    }
    public function course_lesson_details(){
        $course_slug =  $this->input->post('courseSlug');
        $whereConditionArray=  array(
            'course_slug' => $course_slug,
            'status' => 'active'
        );
        $data = $this->m_courses->get_record($whereConditionArray);
        if($data){
            $course_details = $this->crud_model->get_course_by_id($data->id)->row_array();
            $instructor_details = $this->user_model->get_all_user($data->user_id)->row_array();
            
            $total_rating =  $this->crud_model->get_ratings('course', $course_details['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $course_details['id'])->num_rows();
            if ($number_of_ratings > 0) {
              $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
              $average_ceil_rating = 0;
            }
            $course_details['average_ceil_rating'] = $average_ceil_rating;
            $course_details['courseImage'] = $this->crud_model->get_course_thumbnail_url($data->id);
            
            
            $whereConditionArray = array(
                'course_id'=> $data->id
                );
            $lessons = $this->m_lesson->get_records($whereConditionArray);
            $sectionsTemp = array();
            if($lessons){
                foreach ($lessons as $key => $value) {
                    if($value->lesson_type == 'other'){
                    $tmp = explode('.', $value->attachment);
                     $fileExtension = strtolower(end($tmp));
                     $lessons[$key]->fileExtension= $fileExtension;
                 }else{
                    $lessons[$key]->fileExtension = "" ;
                 }

                   $whereConditionArray = array(
                    'id'=> $value->section_id
                    );
                   $section = $this->m_section->get_record($whereConditionArray);
                    array_push($sectionsTemp, $section);
                }
            }
            $new_array = array_map("unserialize", array_unique(array_map("serialize", $sectionsTemp)));
            $sections = array_values($new_array);
            
            $ratings = $this->crud_model->get_ratings('course', $data->id)->result_array();
            foreach ($ratings as $key => $value) {
               $ratings[$key]['userImage'] = $this->user_model->get_user_image_url($value['user_id']);
               $user_details = $this->user_model->get_user($value['user_id'])->row_array();
               $ratings[$key]['userName']  = $user_details['first_name'].' '.$user_details['last_name'];
               $ratings[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            }
            $other_realted_courses = $this->crud_model->get_courses($course_details['category_id'], $course_details['sub_category_id'])->result_array();
            echo json_encode(array('data' =>$course_details , 'lessons'=> $lessons, 'sections'=> $sections,'ratings'=> $ratings ,'instructor_details' =>$instructor_details,'other_realted_courses' => $other_realted_courses));
            return json_encode(array('data' =>$course_details , 'lessons'=> $lessons, 'sections'=> $sections,'ratings'=> $ratings ,'instructor_details' =>$instructor_details, 'other_realted_courses' => $other_realted_courses));
        }else{
            echo json_encode(array('data' =>false , 'lessons'=> false));
            return json_encode(array('data' =>false , 'lessons'=> false));
        }
    }
    public function get_quiz_data()
    {
        $les_id =  $this->input->post('les_id');
        $quiz_questions = $this->crud_model->get_quiz_questions($les_id)->result_array();
        echo json_encode(array('found' =>true , 'quiz_questions' => $quiz_questions));
        return json_encode(array('found' =>true , 'quiz_questions' => $quiz_questions));
        // print_result($quiz_questions);
    }
    public function submit_quiz() {
        $submitted_quiz_info = array();
        $container = array();
        $quiz_id = $this->input->post('lesson_id');
        // print_result($quiz_id);
        $quiz_questions = $this->crud_model->get_quiz_questions($quiz_id)->result_array();
        $total_correct_answers = 0;
        foreach ($quiz_questions as $quiz_question) {
            $submitted_answer_status = 0;
            $correct_answers = json_decode($quiz_question['correct_answers']);
            $submitted_answers = array();
            foreach ($this->input->post($quiz_question['id']) as $each_submission) {
                if (isset($each_submission)) {
                    array_push($submitted_answers, $each_submission);
                }
            }
            sort($correct_answers);
            sort($submitted_answers);
            if ($correct_answers == $submitted_answers) {
                $submitted_answer_status = 1;
                $total_correct_answers++;
            }
            $container = array(
                "question_id" => $quiz_question['id'],
                'submitted_answer_status' => $submitted_answer_status,
                "submitted_answers" => json_encode($submitted_answers),
                "correct_answers"  => json_encode($correct_answers),
            );
            array_push($submitted_quiz_info, $container);
        }
        $page_data['submitted_quiz_info']   = $submitted_quiz_info;
        $page_data['total_correct_answers'] = $total_correct_answers;
        $page_data['total_questions'] = count($quiz_questions);
        foreach ($submitted_quiz_info as $key => $value) {
        $question_details = $this->crud_model->get_quiz_question_by_id($value['question_id'])->row_array();
        
        $options = json_decode($question_details['options']);
        $correct_answers = json_decode($value['correct_answers']);
        
        $submitted_answers = json_decode($value['submitted_answers']);
           $submitted_quiz_info[$key]['title']            = $question_details['title'];
           // echo "<pre>";
           // print_r($options);
           // echo "<pre>";
           // print_r("correct :" .$correct_answers[0]);
           foreach ($options as $key1 => $value1) {
              if( $submitted_answers[0]  ==  ($key1+1) ){
                $submitted_quiz_info[$key]['given_ans'] = $value1;
              }
              if( $correct_answers[0]  ==  ($key1+1) ){
                $submitted_quiz_info[$key]['correct_ans'] = $value1;
              }

              // echo $key+1;
              // echo "<br> value " . $value;
           }
        }
        // print_result($submitted_quiz_info);
        echo json_encode(array('found' =>true ,'submitted_quiz_info'=> $submitted_quiz_info, 'total_questions' => count($quiz_questions), 'total_correct_answers' => $total_correct_answers));
        return json_encode(array('found' =>true ,'submitted_quiz_info'=> $submitted_quiz_info, 'total_questions' => count($quiz_questions), 'total_correct_answers' => $total_correct_answers));

        // echo json_encode($this->load->view('frontend/'.get_frontend_settings('theme').'/quiz_result', $page_data));
    }
    public function search() {
        $search_string = $this->input->post('searchKey');
        $data = $this->crud_model->get_courses_by_search_string($search_string)->result_array();
        
        foreach ($data as $key => $value) {
            $data[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
            $data[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
                $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
                $average_ceil_rating = 0;
            }
            $data[$key]['average_ceil_rating'] = $average_ceil_rating;
        }
        echo json_encode($data);
        return json_encode($data);
    }
    public function category_courses() {
        $search_string = $this->input->post('searchKey');
        $selected_category_id = $this->crud_model->get_category_id($search_string);
        
        $data = $this->crud_model->get_courses_by_category_id($selected_category_id)->result_array();

        foreach ($data as $key => $value) {
            $data[$key]['courseImage'] = $this->crud_model->get_course_thumbnail_url($value['id']);
            $data[$key]['date_add'] = date('D, d-M-Y', $value['date_added']);
            $total_rating =  $this->crud_model->get_ratings('course', $value['id'], true)->row()->rating;
            $number_of_ratings = $this->crud_model->get_ratings('course', $value['id'])->num_rows();
            if ($number_of_ratings > 0) {
                $average_ceil_rating = ceil($total_rating / $number_of_ratings);
            }else {
                $average_ceil_rating = 0;
            }
            $data[$key]['average_ceil_rating'] = $average_ceil_rating;
        }
        echo json_encode($data);
        return json_encode($data);
    }
}
