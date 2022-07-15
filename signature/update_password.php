<?php

require_once("../lib/config.php");
require_once("../lib/constants.php");



if (isset($_POST['password']) && $_POST['reset_link_token'] && $_POST['email']) {
    $email = $_POST['email'];
    $token = $_POST['reset_link_token'];

    $password = md5($_POST['password']);
    $query ="SELECT * FROM `user_master` WHERE `reset_link_token`='" . $token . "' and `email`='" . $email . "'";
    $result = $db->query($query);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        "UPDATE user_master set  password='" . $password . "', reset_link_token='" . NULL . "' ,exp_date='" . NULL . "' WHERE email='" . $email . "'";
        echo '<p>Congratulations! Your password has been updated successfully.</p>';
    } else {
        echo "<p>Something goes wrong. Please try again</p>";
    }
}
?>
