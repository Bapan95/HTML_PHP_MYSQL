<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
require_once('../Classes/PHPExcel/IOFactory.php');
$logged_user_id = my_session('user_id');
if (isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')) {
    $logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$bank_account_id = $_REQUEST['bank_account_id'];
//$sup_cat_id = $_REQUEST['super_category_id'];

$role_id =  my_session('role_id');
$return_data  = array();

// ACTIONS 
if ($action_type === 'BANK_ACC_LIST') {
    $query = "SELECT ba.bank_account_id, ba.account_no, ba.ifsc_no, ba.branch_name, ba.bank_id, bm.bank_name, ba.branch_address, cm.company_name, ba.is_active, ba.created_by, ba.created_ts 
  FROM bank_account_master ba INNER JOIN bank_master bm ON bm.bank_id = ba.bank_id LEFT JOIN company_master cm ON cm.company_id = ba.company_id";
    $result = $db->query($query);

    while ($data = mysqli_fetch_assoc($result)) {
        $ret[] = $data;
    }

    $return_data  = array('status' => true, 'bank_list' => $ret, 'query' => $query);
    echo json_encode($return_data);
} elseif ($action_type == 'UPDATE_SUB_CATEGORY_DATA') {
    $query = "select bank_account_id, account_no, ifsc_no, branch_name, bank_id, branch_address, company_id, is_active, created_by from bank_account_master WHERE bank_account_id = " . $bank_account_id;
    $result = $db->query($query);
    $data = mysqli_fetch_assoc($result);

    $return_data  = array('status' => true, 'bank_account_data' => $data);
    echo json_encode($return_data);
} elseif ($action_type == 'ACTIVATE_SUB_CATEGORY') {
    $query = "UPDATE sub_category_master SET is_active = 1 WHERE sub_category_id = " . $bank_account_id;
    $result = $db->query($query);

    $return_data = array('status' => true, 'message' => 'Category Activated');
    echo json_encode($return_data);
} elseif ($action_type == 'DEACTIVATE_SUB_CATEGORY') {
    $query = "UPDATE sub_category_master SET is_active = 0 WHERE sub_category_id = " . $bank_account_id;
    $result = $db->query($query);

    $return_data = array('status' => true, 'message' => 'Category Deactivated');
    echo json_encode($return_data);
} elseif ($action_type == 'LIST_SUPER_CATEGORIES') {
    $query = "SELECT super_cat_id, super_cat_name FROM super_category_master WHERE is_active = 1 order by super_cat_name";
    $result = $db->query($query);

    if ($result) {
        $ret = [];

        while ($data = mysqli_fetch_assoc($result)) {
            $ret[] = $data;
        }

        $return_data  = array('status' => true, 'super_categories_list' => $ret);
        echo json_encode($return_data);
    }
} elseif ($action_type == 'LIST_ALL_BANK') {
    $bank_account_id = intval($_REQUEST['bank_account_id']);

    $query = "SELECT bank_id,bank_name,bank_head_office_address FROM bank_master";
    // echo $query;die();
    $result = $db->query($query);
    while ($data = mysqli_fetch_assoc($result)) {
        $ret[] = $data;
    }


    $queryk = "SELECT * FROM bank_account_master where bank_account_id=" . $bank_account_id . "";

    $resultk = $db->query($queryk);
    while ($datak = mysqli_fetch_assoc($resultk)) {
        $retk[] = $datak;
    }


    $return_data  = array('status' => true, 'bank_list' => $ret, 'bank_list_id' => $retk);
    echo json_encode($return_data);
} elseif ($action_type == "BRANCH") {
    $bank_id = $_REQUEST['bank_id'];

    $query = "SELECT bank_head_office_address as branch FROM bank_master where bank_id=" . $bank_id . "";
    //echo $query;die;
    // echo $query;die();
    $result = $db->query($query);
    while ($data = mysqli_fetch_assoc($result)) {
        $ret[] = $data;
    }
    $return_data  = array('status' => true, 'branch_list' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == 'LIST_CATEGORIES') {
    $query = "SELECT category_id, category_name FROM category_master WHERE is_active = 1 AND super_cat_id ='" . $sup_cat_id . "' order by category_name";
    $result = $db->query($query);

    if ($result) {
        $ret = [];

        while ($data = mysqli_fetch_assoc($result)) {
            $ret[] = $data;
        }

        $return_data  = array('status' => true, 'categories_list' => $ret);
        echo json_encode($return_data);
    }
} elseif ($action_type == 'NEW_BANK_ACCOUNT') {

    $bank_account = $_REQUEST['bank_account'];
    $ifsc_no = $_REQUEST['ifsc_no'];
    $branch_name = $_REQUEST['branch_name'];
    $bank_id = $_REQUEST['bank_id'];
    $company_id = $_REQUEST['company_id'];
    $branch_address = $_REQUEST['branch_address'];
    //echo json_encode($db->escape_string($branch_address)); exit();
    //echo $cat_id;die();
    $bank_account_id = $_REQUEST['bank_account_id'];

    //echo $bank_account.'--'.$ifsc_no.'--'.$branch_name.'--'.$bank_id.'--hidden_id--'.$bank_account_id;die;
    if (!empty($bank_account_id)) {
        $query = "UPDATE bank_account_master SET account_no = '" . $bank_account . "', ifsc_no = '" . $ifsc_no . "', branch_name = '" . $branch_name . "', bank_id = '" . $bank_id . "', branch_address = '" . $db->escape_string($branch_address) . "', company_id = '" . $company_id . "', updated_by = '" . $logged_user_id . "', updated_ts = NOW() WHERE bank_account_id = '" . $bank_account_id . "';";
        $result = $db->query($query);
        //echo json_encode($query); exit();
        $return_data = array('status' => true, 'id' => 2, 'message' => 'Bank Account Updated Succesfully!');
        echo json_encode($return_data);
    } else {
        $sql = "SELECT COUNT(bank_account_id) AS CNT FROM bank_account_master WHERE bank_account_id = '" . $bank_account_id . "'";
        $res = $db->query($sql);
        $results = mysqli_fetch_assoc($res);
        if ($results['CNT'] > 0) {
            $return_data = array('status' => true, 'id' => 0, 'message' => 'Bank Account Already Exists');
            echo json_encode($return_data);
        } else {
            $query = "INSERT INTO bank_account_master(account_no, ifsc_no, branch_name, bank_id, branch_address, company_id, created_by, created_ts) 
                        VALUES('" . $bank_account . "', '" . $ifsc_no . "', '" . $branch_name . "', '" . $bank_id . "', '" . $db->escape_string($branch_address) . "', '" . $company_id . "', '" . $logged_user_id . "', NOW());";
            $result = $db->query($query);

            $return_data = array('status' => true, 'id' => 1, 'message' => 'Bank Account Created!');
            echo json_encode($return_data);
        }
    }
}
