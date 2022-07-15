<?php

require_once("../lib/config.php");
require_once("../lib/constants.php");
$action_type = $_REQUEST['action_type'];
$return_data  = array();
if ($action_type == "update_pass") {
  $token = $_REQUEST['token'];
  //   echo $user_id;die;
  $password = $_REQUEST['password'];
  // echo $password;
  $confirmpassword = $_REQUEST['confirmpassword'];
  // echo $confirmpassword;
  $encrypted_pwd = md5($password);
  // echo $encrypted_pwd;die;

  $encrypted_con_pwd = md5($confirmpassword);

  if ($encrypted_pwd == $encrypted_con_pwd) {
    $query = "update user_master set password= '" . $encrypted_pwd . "' WHERE reset_link_token= '" . $token . "'";
    // echo $query;die;
    $result = $db->query($query);
    //    echo $result;die;

    if ($result) {
      $return['key'] = 'S';
      $return['msg'] = 'successfully change your password';
      echo json_encode($return);
      //     echo "Check Your Email and Click on the link sent to your email";
      // } else {
      //     echo "Mail Error - >" . $mail->ErrorInfo;
      // }
    } else {
      $return['key'] = 'E';
      $return['msg1'] = 'credential not match';
      echo json_encode($return);
      // echo "Invalid Email Address. Go back";
    }

    // echo"success";
  } else {

    echo 'password and confirm password did not match';
  }
}
