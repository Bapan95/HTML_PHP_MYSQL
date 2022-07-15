<?php

require_once("../lib/config.php");
require_once("../lib/constants.php");



$user_id = $_REQUEST['user_id'];
$token = $_REQUEST['token'];

$query = "SELECT * FROM `user_master` WHERE `user_id`='" . $user_id . "'";

$result = $db->query($query);
$row = mysqli_fetch_assoc($result);
$user = $row['user_id'];
$otp = $row['reset_link_token'];

if ($user_id == $user && $token == $otp) {
    $return['key'] = 'S';
    $return['msg'] = 'otp matched and page is redirect to reset password page';
} else {
    $return['key'] = 'E';
    $return['msg'] = 'please enter the valid otp';
}

echo json_encode($return);
