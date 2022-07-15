<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
$logged_user_id = my_session('user_id');
if (isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')) {
    $logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$return_data  = array();

if ($action_type == "GET_VENDOR_ADVANCE") {
    $vendor_id = intval($_POST['vendor_id']);

    $adv_row = array();
    $query = "SELECT get_vendor_advance_func({$vendor_id}) as advance_amt;";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        $adv_row = mysqli_fetch_assoc($result);
        $return_data  = array('status' => true, 'adv_row' => $adv_row, 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "GET_VENDOR_UNPAID_PURCHASE") {
    $vendor_id = intval($_POST['vendor_id']);

    $purchase_list = array();
    $query = "CALL get_vendor_unpaid_purchase_proc({$vendor_id});";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $purchase_list[] = $row;
        }
        $return_data  = array('status' => true, 'purchase_list' => $purchase_list, 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "MAKE_PAYMENT") {
    $client_id = intval($_POST['client_id']);
    $payment_type = trim($_POST['payment_type']);
    $payment_date = trim($_POST['payment_date']);
    $date = trim($_POST['date']);
    $bank_id = trim($_POST['bank_id']);
    $branch = trim($_POST['branch']);
    $amount = floatval($_POST['amount']);
    $transaction_no = trim($_POST['reference_no']);
    $payment_mode = $_POST['payment_mode'];
    $account_no = trim($_POST['account_no']);
    // echo($account_no);
    // die();
    if ($_POST['tds_pct'] == '0' || $_POST['tds_pct'] == '') {
        $tds_pct = $_POST['tds_pct_agt'];
    } else {
        $tds_pct = $_POST['tds_pct'];
    }

    if ($_POST['tds_amt'] == '0' || $_POST['tds_amt'] == '') {
        $tds_amt = $_POST['tds_amt_agt'];
    } else {
        $tds_amt = $_POST['tds_amt'];
    }


    /*if($_POST['payment_amt'] == '0' || $_POST['payment_amt'] == '' ){
		$payment_amt = $_POST['payment_amt_agt'];
	}
	else{*/
    $payment_amt = $_POST['payment_amt_agt'];
    //}

    $total_amt = $_POST['total_amt'];


    //$tds_amt = $_POST['tds_amt'];
    //$tds_amt = $_POST['tds_amt_agt'];
    //$payment_amt = $_POST['payment_amt'];
    //$payment_amt = $_POST['payment_amt_agt'];

    //echo $payment_amt; die;
    //$file_name="";
    $payment_id = 0;
    $selected_invoice = $_POST['selected_invoice'];
    $selected_invoice_str = '';
    if (!empty($selected_invoice)) {
        $selected_invoice_str = implode(',', $selected_invoice);
    }

    $query = "CALL add_edit_client_receipt_change_proc('{$payment_id}', {$client_id}, '{$payment_type}', '{$payment_date}',{$amount},'{$bank_id}','{$branch}','{$account_no}',
	'{$transaction_no}','{$selected_invoice_str}',{$logged_user_id},'{$payment_mode}','{$date}','{$tds_pct}','{$tds_amt}','{$payment_amt}','{$total_amt}');";
    // echo $query; die;   
    $result = $db->query($query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $return_data  = array('status' => true, 'msg' => $row['msg'], 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'msg' => '', 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "MAKE_PAYMENT_CREDIT_NOTE") {
    //echo json_encode($_REQUEST); die();
    $invoice_id = intval($_POST['invoice_id']);
    $client_id = intval($_POST['client_id']);
    $credit_note_no = trim($_POST['credit_note_no']);
    $credit_note_date = trim($_POST['credit_note_date']);
    $particulars = trim($_POST['particulars']);
    $invoice_type = trim($_POST['invoice_type']);
    $invoice_no = trim($_POST['invoice_no']);
    $invoice_date = trim($_POST['invoice_date']);
    $gross_total = trim($_POST['gross_total']);
    $cgst_percentage = trim($_POST['cgst_percentage']);
    $sgst_percentage = trim($_POST['sgst_percentage']);
    $igst_percentage = trim($_POST['igst_percentage']);
    $total_cgst = trim($_POST['total_cgst']);
    $total_sgst = trim($_POST['total_sgst']);
    $total_igst = trim($_POST['total_igst']);
    $amount = trim($_POST['amount']);

    $query = "CALL add_edit_client_credit_note_proc('{$invoice_id}','{$client_id}', '{$credit_note_no}', '{$credit_note_date}','{$particulars}','{$invoice_type}','{$invoice_no}',
	'{$invoice_date}','{$gross_total}','{$cgst_percentage}','{$sgst_percentage}','{$igst_percentage}','{$total_cgst}','{$total_sgst}','{$total_igst}','{$amount}','{$logged_user_id}');";

    //echo $query; die;
    $result = $db->query($query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $return_data  = array('status' => true, 'msg' => $row['msg'], 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'msg' => '', 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "MAKE_PAYMENT_DEBIT_NOTE") {
    //echo json_encode($_REQUEST); die();
    $invoice_id = intval($_POST['invoice_id']);
    $vendor_id = intval($_POST['vendor_id']);
    $debit_note_no = trim($_POST['debit_note_no']);
    $debit_note_date = trim($_POST['debit_note_date']);
    $particulars = trim($_POST['particulars']);
    $invoice_type = trim($_POST['invoice_type']);
    $invoice_no = trim($_POST['invoice_no']);
    $invoice_date = trim($_POST['invoice_date']);
    $gross_total = trim($_POST['gross_total']);
    $cgst_percentage = trim($_POST['cgst_percentage']);
    $sgst_percentage = trim($_POST['sgst_percentage']);
    $igst_percentage = trim($_POST['igst_percentage']);
    $total_cgst = trim($_POST['total_cgst']);
    $total_sgst = trim($_POST['total_sgst']);
    $total_igst = trim($_POST['total_igst']);
    $amount = trim($_POST['amount']);

    $query = "CALL add_edit_vendor_debit_note_proc('{$invoice_id}','{$vendor_id}', '{$debit_note_no}', '{$debit_note_date}','{$particulars}','{$invoice_type}','{$invoice_no}',
	'{$invoice_date}','{$gross_total}','{$cgst_percentage}','{$sgst_percentage}','{$igst_percentage}','{$total_cgst}','{$total_sgst}','{$total_igst}','{$amount}','{$logged_user_id}');";

    //echo $query; die;
    $result = $db->query($query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $return_data  = array('status' => true, 'msg' => $row['msg'], 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'msg' => '', 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "get_resepet_invoice") {
    $vendor_id = intval($_POST['vendor_id']);

    $purchase_list = array();
    $query = "CALL resecved_invoice_payment({$vendor_id});";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $purchase_list[] = $row;
        }
        $return_data  = array('status' => true, 'purchase_list' => $purchase_list, 'qry' => $query);
    } else {
        $return_data  = array('status' => false, 'qry' => $query);
    }
    echo json_encode($return_data);
} elseif ($action_type == "CLIENT_LIST") {
    $query = "SELECT c.client_id, c.client_name, c.alias_name, c.client_code, c.parent_client_id, IFNULL(pc.client_name, '') parent_client, 
	c.address, c.state_id, st.state_name, st.state_code, c.district_id, dt.district_name, c.village_town, c.pin_code, c.pan, c.gstin, 
	IFNULL(ctt.contact_no_1, '') contact_no_1, IFNULL(ctt.contact_no_2, '') contact_no_2, IFNULL(ctt.email_id_1, '') email_id_1, IFNULL(ctt.email_id_2, '') email_id_2 
	FROM client_master c
	LEFT JOIN client_master pc ON c.parent_client_id = pc.client_id 
	LEFT JOIN contact_master ctt ON ctt.contact_ref_id = c.client_id AND ctt.contact_type = 'C' 
	LEFT JOIN state_master st ON c.state_id = st.state_id 
	LEFT JOIN district_master dt ON c.district_id = dt.district_id 
	WHERE c.client_type != 'V'";
    // print_r($query);die;
    $result = $db->query($query);
    while ($data = mysqli_fetch_assoc($result)) {
        $ret[] = $data;
    }
    $return_data  = array('status' => true, 'vendor_list' => $ret);
    echo json_encode($return_data);
}
