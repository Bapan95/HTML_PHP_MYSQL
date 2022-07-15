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
} elseif ($action_type == "GET_VENDOR_UNPAID_PO") {
	$vendor_id = intval($_POST['vendor_id']);

	$po_list = array();
	$query = "CALL get_vendor_unpaid_po_proc({$vendor_id});";
	$result = $db->query($query);
	if ($result->num_rows > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$po_list[] = $row;
		}
		$return_data  = array('status' => true, 'po_list' => $po_list, 'qry' => $query);
	} else {
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
} elseif ($action_type == "MAKE_PAYMENT") {
	//echo json_encode($_REQUEST); exit();
	$vendor_id = intval($_POST['vendor_id']);
	$payment_type = trim($_POST['payment_type']);
	$payment_date = trim($_POST['payment_date']);
	$total_amount = floatval($_POST['total_amount']);
	$tds_percent = floatval($_POST['tds_percent']);
	$cash_amount = floatval($_POST['cash_amount']);
	$cheque_no = trim($_POST['cheque_no']);
	$cheque_date = trim($_POST['cheque_date']);
	$bank_name = trim($_POST['bank_name']);
	$cheque_amount = floatval($_POST['cheque_amount']);
	$transaction_no = trim($_POST['transaction_no']);
	$transfer_bank = trim($_POST['transfer_bank']);
	$transfer_amount = floatval($_POST['transfer_amount']);
	$remarks = trim($_POST['remarks']);
	$invoice_number = $_POST['invoice_number'];
	$file_name = "";
	$payment_id = 0;
	$selected_po = $_POST['selected_po'];
	$selected_po_str = '';
	if ($_FILES['invoice_upload']) {
		$file = $_FILES['invoice_upload']['name'];
		$file_ext = strtolower(end(explode(".", $file)));
		$allowed_ext = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx");
		if (in_array($file_ext, $allowed_ext)) {
			$file_name = 'po_invoice_file_' . time() . '.' . $file_ext;
			$sourcePath = $_FILES['invoice_upload']['tmp_name'];
			$targetPath = "../upload_file/" . $file_name;
			move_uploaded_file($sourcePath, $targetPath);
		}
	}

	if (!empty($selected_po)) {
		$selected_po_str = implode(',', $selected_po);
	}

	$query = "CALL add_edit_vendor_payment_proc('{$payment_id}', {$vendor_id}, '{$payment_type}', '{$payment_date}', {$total_amount}, {$tds_percent}, {$cash_amount}, '{$cheque_no}', '{$cheque_date}', '{$bank_name}', {$cheque_amount}, '{$transaction_no}', '{$transfer_bank}', {$transfer_amount}, '{$db->escape_string($remarks)}', '{$selected_po_str}', '{$invoice_number}', '{$file_name}', {$logged_user_id});";
	$result = $db->query($query);
	if ($result) {
		$row = mysqli_fetch_assoc($result);
		$return_data  = array('status' => true, 'msg' => $row['msg'], 'qry' => $query);
	} else {
		$return_data  = array('status' => false, 'msg' => '', 'qry' => $query);
	}
	echo json_encode($return_data);
} elseif ($action_type == "get_client_unpaid_invoice") {
	$client_id = intval($_POST['client_id']);

	$purchase_list = array();
	$query = "CALL get_client_unpaid_invoice_proc({$client_id});";

	// echo $query; die;
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
} elseif ($action_type == "GET_CLIENT_ADVANCE") {
	$client_id = intval($_POST['client_id']);

	$adv_row = array();
	$query = "SELECT get_client_advance_func({$client_id}) as advance_amt;";
	$result = $db->query($query);
	if ($result->num_rows > 0) {
		$adv_row = mysqli_fetch_assoc($result);
		$return_data  = array('status' => true, 'adv_row' => $adv_row, 'qry' => $query);
	} else {
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}
