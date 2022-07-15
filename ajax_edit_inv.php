<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
$logged_user_id = my_session('user_id');
if (isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')) {
    $logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$return_data  = array();
/*
echo json_encode($_REQUEST);
exit();
*/

if ($action_type == "Edit_OHH_invoice") {
    //  echo "partha";
    /* echo "<pre>";
	 print_r($_REQUEST);
	 die;*/
    $invoice_edit_string = '';
    $invoice_detail_id_id_count = count($_REQUEST['invoice_detail_id']);
    $invoice_detail_id = $_REQUEST['invoice_detail_id'];
    $site_name = $_REQUEST['site_name'];
    $site_id = $_REQUEST['site_id'];
    $invoice_no = $_REQUEST['invoice_no'];

    $invoice_date = $_REQUEST['invoice_date'];
    //    echo $invoice_date;die;
    $po_no = $_REQUEST['po_no'];
    $email_date = $_REQUEST['email_date'];
    //   echo $email_date;
    $hidden_invoice_id = $_REQUEST['hidden_invoice_id'];
    $action_date = $_REQUEST['action_date'];

    //echo $invoice_date,$email_date; die;


    for ($i = 0; $i < $invoice_detail_id_id_count; $i++) {
        $invoice_edit_string .= $invoice_detail_id[$i] . '|' . $site_id[$i] . '|' . $site_name[$i] . '#';
    }

    $invoice_edit_string = rtrim($invoice_edit_string, '#');


    $upd_query = "CALL edit_inv_proc('" . $hidden_invoice_id . "','" . $invoice_no . "','" . $invoice_date . "','" . $po_no . "','" . $email_date . "', '" . $invoice_edit_string . "', '" . $logged_user_id . "','" . $action_date . "'); ";

    // echo $upd_query;die;


    $res = $db->query($upd_query);
    // echo $res; die;
    if ($res) {
        $msg = "Updated sucessfully !";
        $id = 1;
    } else {
        $msg = "Try again !";
    }
    //echo $msg; die;
    $return_data  = array('status' => true, 'upd_query' => $upd_query, 'id' => $id, 'page_id' => $hidden_invoice_id);
    echo json_encode($return_data);
}


if ($action_type == "Edit_Printing_invoice") {
    //echo "partha"; die;
    /* echo "<pre>";
	 print_r($_REQUEST);
	 die;*/
    $invoice_edit_string = '';
    $invoice_detail_id_id_count = count($_REQUEST['invoice_detail_id']);
    $invoice_detail_id = $_REQUEST['invoice_detail_id'];
    $site_name = $_REQUEST['site_name'];
    $site_id = $_REQUEST['site_id'];
    $invoice_no = $_REQUEST['invoice_no'];
    $invoice_date = $_REQUEST['invoice_date'];
    $po_no = $_REQUEST['po_no'];
    $email_date = $_REQUEST['email_date'];


    $hidden_invoice_id = $_REQUEST['hidden_invoice_id'];
    $action_date = $_REQUEST['action_date'];

    //echo $invoice_detail_id;	 die;


    for ($i = 0; $i < $invoice_detail_id_id_count; $i++) {
        $invoice_edit_string .= $invoice_detail_id[$i] . '|' . $site_id[$i] . '|' . $site_name[$i] . '#';
    }
    //echo $invoice_edit_string; die;
    $invoice_edit_string = rtrim($invoice_edit_string, '#');


    $upd_query = "CALL edit_printing_inv_proc('" . $hidden_invoice_id . "','" . $invoice_no . "','" . $invoice_date . "','" . $po_no . "','" . $email_date . "', '" . $invoice_edit_string . "', '" . $logged_user_id . "','" . $action_date . "'); ";

    //echo $upd_query;die;


    $res = $db->query($upd_query);
    // echo $res; die;
    if ($res) {
        $msg = "Updated sucessfully !";
        $id = 1;
    } else {
        $msg = "Try again !";
    }
    //echo $msg; die;
    $return_data  = array('status' => true, 'upd_query' => $upd_query, 'id' => $id, 'page_id' => $hidden_invoice_id);
    echo json_encode($return_data);
}

if ($action_type == "Edit_Mounting_invoice") {
    //echo "partha"; die;
    /* echo "<pre>";
	 print_r($_REQUEST);
	 die;*/
    $invoice_edit_string = '';
    $invoice_detail_id_id_count = count($_REQUEST['invoice_details_id']);
    $invoice_detail_id = $_REQUEST['invoice_details_id'];
    $site_name = $_REQUEST['site_name'];
    $site_id = $_REQUEST['site_id'];
    $invoice_no = $_REQUEST['invoice_no'];
    $invoice_date = $_REQUEST['invoice_date'];

    $po_no = $_REQUEST['po_no'];
    $email_date = $_REQUEST['email_date'];

    $hidden_invoice_id = $_REQUEST['hidden_invoice_id'];
    $action_date = $_REQUEST['action_date'];

    //echo $invoice_date,$email_date; die;


    for ($i = 0; $i < $invoice_detail_id_id_count; $i++) {
        $invoice_edit_string .= $invoice_detail_id[$i] . '|' . $site_id[$i] . '|' . $site_name[$i] . '#';
    }
    // echo $invoice_edit_string;die;
    $invoice_edit_string = rtrim($invoice_edit_string, '#');


    $upd_query = "CALL edit_mounting_inv_proc('" . $hidden_invoice_id . "','" . $invoice_no . "','" . $invoice_date . "','" . $po_no . "','" . $email_date . "', '" . $invoice_edit_string . "', '" . $logged_user_id . "','" . $action_date . "'); ";

    //echo $upd_query;die;


    $res = $db->query($upd_query);
    // echo $res; die;
    if ($res) {
        $msg = "Updated sucessfully !";
        $id = 1;
    } else {
        $msg = "Try again !";
    }
    //echo $msg; die;
    $return_data  = array('status' => true, 'upd_query' => $upd_query, 'id' => $id, 'page_id' => $hidden_invoice_id);
    echo json_encode($return_data);
}

if ($action_type == "Edit_Manual_invoice") {
    //echo "partha"; die;
    /* echo "<pre>";
	 print_r($_REQUEST);
	 die;*/
    $invoice_edit_string = '';
    $invoice_detail_id_id_count = count($_REQUEST['invoice_detail_id']);
    $invoice_detail_id = $_REQUEST['invoice_detail_id'];
    $site_name = $_REQUEST['site_name'];
    $invoice_no = $_REQUEST['invoice_no'];
    $invoice_date = $_REQUEST['invoice_date'];

    $po_no = $_REQUEST['po_no'];
    $email_date = $_REQUEST['email_date'];

    $hidden_invoice_id = $_REQUEST['hidden_invoice_id'];
    $action_date = $_REQUEST['action_date'];

    //echo $invoice_date,$email_date; die;


    for ($i = 0; $i < $invoice_detail_id_id_count; $i++) {
        $invoice_edit_string .= $invoice_detail_id[$i] . '|' . $site_name[$i] . '#';
    }
    //echo $invoice_edit_string;die;
    $invoice_edit_string = rtrim($invoice_edit_string, '#');


    $upd_query = "CALL edit_manual_inv_proc('" . $hidden_invoice_id . "','" . $invoice_no . "','" . $invoice_date . "','" . $po_no . "','" . $email_date . "', '" . $invoice_edit_string . "', '" . $logged_user_id . "','" . $action_date . "'); ";

    //echo $upd_query;die;


    $res = $db->query($upd_query);
    // echo $res; die;
    if ($res) {
        $msg = "Updated sucessfully !";
        $id = 1;
    } else {
        $msg = "Try again !";
    }
    //echo $msg; die;
    $return_data  = array('status' => true, 'upd_query' => $upd_query, 'id' => $id, 'page_id' => $hidden_invoice_id);
    echo json_encode($return_data);
}
