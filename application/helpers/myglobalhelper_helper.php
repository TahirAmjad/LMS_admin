<?php defined('BASEPATH') OR exit('No direct script access allowed');

function encode($value)
{
    if (!$value) {
        return false;
    }
    $ci =& get_instance();
    return strtr($ci->encryption->encrypt($value), array('+' => '--1', '=' => '--2', '/' => '--3'));
}

function decode($value)
{
    if (!$value) {
        return false;
    }
    $ci =& get_instance();
    return $ci->encryption->decrypt(strtr($value, array('--1' => '+', '--2' => '=', '--3' => '/')));
}

function print_result($result)
{
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    die();
}

function print_api_result($result)
{
    $res = array(
        'status' => "success",
        'message' => $result
    );

    echo json_encode($res);
    die();
}