<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;




// public api

$route['login'] = 'public/api/c_auth/do_login';

$route['get_courses'] = 'public/api/c_courses/get_courses';
$route['get_top_courses'] = 'public/api/c_courses/get_top_courses';
$route['get_load_more_course'] = 'public/api/c_courses/get_load_more_course';
$route['course_details'] = 'public/api/c_courses/course_details';
$route['get_enrolled'] = 'public/api/c_courses/enrol_student_react';
$route['rating_and_reviews'] = 'public/api/c_courses/rating_and_reviews';
$route['course_lesson_details'] = 'public/api/c_courses/course_lesson_details';
$route['get_quiz_data']= 'public/api/c_courses/get_quiz_data';
$route['submit_quiz']= 'public/api/c_courses/submit_quiz';

$route['search']= 'public/api/c_courses/search';

$route['category_courses']= 'public/api/c_courses/category_courses';

$route['get_menu_categories'] = 'public/api/c_courses/get_menu_categories';

// profile 

$route['get_profile_info'] = 'public/api/c_user/get_profile_info';

$route['save_profile_info'] = 'public/api/c_user/save_profile_info';

$route['register'] ="public/api/c_auth/register";

$route['my_courses'] = 'public/api/c_courses/my_courses';
