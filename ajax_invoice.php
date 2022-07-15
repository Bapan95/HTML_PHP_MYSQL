<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
$logged_user_id = my_session('user_id');
if (isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')) {
  $logged_user_id = $_REQUEST['user_id'];
}
$role_id =  my_session('role_id');
$action_type = $_REQUEST['action_type'];
$return_data  = array();
$booking_id = $_REQUEST['booking_id'];

//echo $booking_id;die;
$total = $_REQUEST['total'];
$discount_amt = $_REQUEST['discount_amt'];
$final_amount = $_REQUEST['final_amount'];
$cgst = $_REQUEST['cgst'];
$sgst = $_REQUEST['sgst'];
$igst = $_REQUEST['igst'];
$tot_amt = $_REQUEST['tot_amt'];
$book_date = $_REQUEST['book_date'];
//$job_no=$_REQUEST['job_no'];
$dis_pst = $_REQUEST['dis_pst'];
$state = $_REQUEST['state'];

$billing_from_2 = $_REQUEST['billing_from_2'];
//$billing_from_2=date('Y-m-d', $billing_from_2);

$po_no = $_REQUEST['po_no'];

//echo $_REQUEST['billing_from_2'];
//echo $state; die;

$invoice_id = $_REQUEST['invoice_id'];
$name = $_REQUEST['name'];
if ($action_type == "INVOICE_LISTING") {

  $query = "SELECT 'Booking Invoice' invoice_type, invoice_id, date_format(generated_date, '%d-%m-%Y') as invoice_date, payable_amount, invoice_no, (cgst_amount + sgst_amount + igst_amount) as gst_amt, gross_amount AS gross_total, status cancel_status 
  FROM ooh_invoice_header WHERE booking_id = '" . $booking_id . "' AND invoice_no IS NOT NULL ORDER BY 2";
  // echo $query;
  // die;
  // $query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='".$booking_id."'";
  $result = $db->query($query);
  if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {
      $ret[] = $data;
    }
  }
  $booking_header_query = "SELECT date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.billing_to,'%d-%m-%Y') billing_to FROM booking_header bh WHERE bh.booking_id='" . $booking_id . "'";
  $booking_header_result = $db->query($booking_header_query);
  if ($booking_header_result) {
    $booking_header_data = mysqli_fetch_assoc($booking_header_result);
  }
  $return_data  = array('status' => true, 'invoice_list' => $ret, 'booking_header_data' => $booking_header_data);
  echo json_encode($return_data);
} else if ($action_type == "MANUAL_INVOICE_LISTING") {
  $query = "SELECT invoice_header_id as invoice_id, invoice_no, ifnull(date_format(invoice_date,'%d-%m-%Y'),'') invoice_date, gross_total, invoice_no, (total_cgst + total_sgst + total_igst) as gst_amt, ifnull(discount_amount,'0') as discount_amount,payable_amount,cancel_status 
  FROM manual_invoice_header";
  // $query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='".$booking_id."'";
  $result = $db->query($query);
  if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {
      $ret[] = $data;
    }
  }
  $return_data  = array('status' => true, 'manual_invoice_list' => $ret);
  echo json_encode($return_data);
} else if ($action_type == "MANUAL_ESTIMATE_LISTING") {
  $query = "SELECT estimate_header_id as invoice_id, invoice_no, ifnull(date_format(invoice_date,'%d-%m-%Y'),'') invoice_date, gross_total, invoice_no, (total_cgst + total_sgst + total_igst) as gst_amt, ifnull(discount_amount,'0') as discount_amount,payable_amount,cancel_status 
  FROM manual_estimate_header";
  // $query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='".$booking_id."'";
  $result = $db->query($query);
  if ($result) {
    while ($data = mysqli_fetch_assoc($result)) {
      $ret[] = $data;
    }
  }
  $return_data  = array('status' => true, 'manual_invoice_list' => $ret);
  echo json_encode($return_data);
} elseif ($action_type == "get_data") {
  $client_id = $_REQUEST['client_id'];

  $qry = "select client_name,client_id,address,pan,gstin,state_id FROM client_master WHERE client_id='" . $client_id . "'";
  $result = $db->query($qry);
  $data = mysqli_fetch_assoc($result);
  $return_data  = array('status' => true, 'clientdata' => $data);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_TEMP_INVOICE") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['qty_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die;
  $query = "CALL generate_invoice_detail_tmp_proc('{$qry_string}', '{$col_sep}', '{$row_sep}');";
  // echo $query;
  // die;
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'booking_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_TEMP_CONTRACT") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['p_rate_' . $site_id])  . $col_sep . floatval($_REQUEST['m_rate_' . $site_id])  . $col_sep . floatval($_REQUEST['qty_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die;
  $query = "CALL generate_cintract_tmp_proc('{$qry_string}', '{$col_sep}', '{$row_sep}');";
  //echo json_encode(array('query' => $query)); exit(); 
  //echo $query;die;
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'booking_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_INVOICE") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $inv_type = $_REQUEST['inv_type'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];
  $dis_type = $_REQUEST['dis_type'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    //$qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id]) . $row_sep;
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['qty_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  $query = "CALL generate_booking_invoice_proc(
	  '{$booking_id}', '{$invoice_from}', '{$invoice_to}',  '{$db->escape_string($invoice_no)}', '{$db->escape_string($po_no)}', '{$db->escape_string($inv_type)}', '{$db->escape_string($email_date)}', 
	  '{$db->escape_string($hsn_sac_no)}', '{$gst_type}', '{$gst_p}', '{$db->escape_string($remarks)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}', '{$dis_type}', '{$dis_amt_per}', '{$bank_acc_no}'
	);";
  // echo $query;
  // die;
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);
  echo json_encode($return_data);
}
//

elseif ($action_type == "ADD_EDIT_CONTRACT") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $inv_type = $_REQUEST['inv_type'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];
  $dis_type = $_REQUEST['dis_type'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    //$qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id]) . $row_sep;
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['p_rate_' . $site_id])  . $col_sep . floatval($_REQUEST['m_rate_' . $site_id])  . $col_sep . floatval($_REQUEST['qty_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  $query = "CALL generate_contract_proc(
	  '{$booking_id}', '{$invoice_from}', '{$invoice_to}',  '{$db->escape_string($invoice_no)}', '{$db->escape_string($po_no)}', '{$db->escape_string($inv_type)}', '{$db->escape_string($email_date)}', 
	  '{$db->escape_string($hsn_sac_no)}', '{$gst_type}', '{$gst_p}', '{$db->escape_string($remarks)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}', '{$dis_type}', '{$dis_amt_per}', '{$bank_acc_no}'
	);";
  //echo json_encode(array('query' => $query)); exit();
  //echo $query; die; 
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);
  echo json_encode($return_data);
}
//for mounting

elseif ($action_type == "ADD_TEMP_INVOICE_FOR_MOUNTING") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]) . $col_sep . floatval($_REQUEST['final_comm_' . $site_id]) . $col_sep . floatval($_REQUEST['total_comm_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die; 
  $query = "CALL generate_mounting_detail_tmp_proc('{$qry_string}', '{$col_sep}', '{$row_sep}');";
  //echo $query;die; 
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'mounting_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_MOUNTING") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  //$invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $inv_type = $_REQUEST['inv_type'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];
  $dis_type = $_REQUEST['dis_type'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    //$qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id]) . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]) . $col_sep . floatval($_REQUEST['final_comm_' . $site_id]) . $col_sep . floatval($_REQUEST['total_comm_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  $query = "CALL generate_mounting_invoice_proc(
	  '{$booking_id}','{$db->escape_string($invoice_no)}', '{$db->escape_string($po_no)}', '{$db->escape_string($inv_type)}', '{$db->escape_string($email_date)}', 
	  '{$db->escape_string($hsn_sac_no)}', '{$gst_type}', '{$gst_p}', '{$db->escape_string($remarks)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}', '{$dis_type}', '{$dis_amt_per}', '{$bank_acc_no}'
	);";

  //echo $query;die;
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_TEMP_INVOICE_FOR_PRINTING") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  $invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];
  $campaign_name = $_REQUEST['campaign_name'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {

    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]) . $col_sep . floatval($_REQUEST['final_comm_' . $site_id]) . $col_sep . floatval($_REQUEST['total_comm_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die; 
  $query = "CALL generate_printing_detail_tmp_proc('{$qry_string}', '{$col_sep}', '{$row_sep}');";
  //echo $query;die; 
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'printing_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_PRINTING") {
  //echo json_encode($_REQUEST); exit();
  $booking_id = intval($_REQUEST['booking_id']);
  $invoice_from = $_REQUEST['invoice_from'];
  //$invoice_to = $_REQUEST['invoice_to'];
  $invoice_no = $_REQUEST['invoice_no'];
  $po_no = $_REQUEST['po_no'];
  $inv_type = $_REQUEST['inv_type'];
  $email_date = $_REQUEST['email_date'];
  $hsn_sac_no = $_REQUEST['hsn_sac_no'];
  $gst_type = $_REQUEST['gst_type'];
  $gst_p = floatval($_REQUEST['gst_p']);
  $remarks = $_REQUEST['remarks'];
  $dis_type = $_REQUEST['dis_type'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];
  $campaign_name = $_REQUEST['campaign_name'];

  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    //$qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id]) . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] .$col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
    $qry_string .= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]) . $col_sep . floatval($_REQUEST['final_comm_' . $site_id]) . $col_sep . floatval($_REQUEST['total_comm_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] . $col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  $query = "CALL generate_printing_invoice_proc(
	  '{$booking_id}','{$db->escape_string($invoice_no)}', '{$db->escape_string($po_no)}', '{$db->escape_string($inv_type)}', '{$db->escape_string($email_date)}', 
	  '{$db->escape_string($hsn_sac_no)}', '{$gst_type}', '{$gst_p}', '{$db->escape_string($remarks)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}', '{$dis_type}', '{$dis_amt_per}', '{$bank_acc_no}','{$campaign_name}'
	);";

  //echo $query;die; 
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_TEMP_INVOICE_FOR_MANUAL") {
  //echo json_encode($_REQUEST); exit();
  //$booking_id = intval($_REQUEST['booking_id']);
  $client = $_REQUEST['client'];
  $b_name = $_REQUEST['b_name'];
  $b_address = $_REQUEST['b_address'];
  $gstin = $_REQUEST['gstin'];
  $pan_no = $_REQUEST['pan_no'];
  $inv_no = $_REQUEST['inv_no'];
  $email_date = $_REQUEST['email_date'];
  $po_no = $_REQUEST['po_no'];
  $po_email_date = $_REQUEST['po_email_date'];
  $gst_per = $_REQUEST['gst_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];




  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . $db->escape_string($_REQUEST['description_' . $site_id])  . $col_sep . $_REQUEST['hsn_sac_' . $site_id] . $col_sep . $db->escape_string($_REQUEST['size_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['rate_' . $site_id] . $col_sep . $_REQUEST['amount_' . $site_id] . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep .floatval($_REQUEST['final_comm_' . $site_id]). $col_sep.floatval($_REQUEST['total_comm_' . $site_id]). $col_sep. $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] .$col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die;  
  $query = "CALL generate_manual_detail_tmp_proc('{$qry_string}', '{$col_sep}', '{$row_sep}','{$client}','{$b_name}','{$b_address}','{$gstin}','{$pan_no}','{$inv_no}','{$email_date}','{$po_no}','{$po_email_date}','{$bank_acc_no}');";
  //echo $query;die;  
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'manual_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "MANUAL_ESTIMATE") {
  //echo json_encode($_REQUEST); exit();
  //$booking_id = intval($_REQUEST['booking_id']);
  $client = $_REQUEST['client'];
  $b_name = $_REQUEST['b_name'];
  $b_address = $_REQUEST['b_address'];
  $gstin = $_REQUEST['gstin'];
  $pan_no = $_REQUEST['pan_no'];
  $inv_no = $_REQUEST['inv_no'];
  $email_date = $_REQUEST['email_date'];
  $po_no = $_REQUEST['po_no'];
  $po_email_date = $_REQUEST['po_email_date'];
  $gst_per = $_REQUEST['gst_per'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];




  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . $db->escape_string($_REQUEST['description_' . $site_id])  . $col_sep . $_REQUEST['hsn_sac_' . $site_id] . $col_sep . $db->escape_string($_REQUEST['size_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['rate_' . $site_id] . $col_sep . $_REQUEST['amount_' . $site_id] . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep .floatval($_REQUEST['final_comm_' . $site_id]). $col_sep.floatval($_REQUEST['total_comm_' . $site_id]). $col_sep. $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] .$col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);
  //echo $qry_string; die;  
  $query = "CALL generate_manual_estimate('{$qry_string}', '{$col_sep}', '{$row_sep}','{$client}','{$b_name}','{$b_address}','{$gstin}','{$pan_no}','{$inv_no}','{$email_date}','{$po_no}','{$po_email_date}','{$bank_acc_no}');";
  //echo $query;die;  
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  while ($result = mysqli_fetch_assoc($rs)) {
    $data[] = $result;
  }
  $return_data = array('status' => true, 'manual_list_tmp' => $data, 'query' => $query);
  echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_MANUAL") {
  //echo json_encode($_REQUEST); exit();
  //$booking_id = intval($_REQUEST['booking_id']);
  $client = $_REQUEST['client'];
  $b_name = $_REQUEST['b_name'];
  $b_address = $_REQUEST['b_address'];
  $gstin = $_REQUEST['gstin'];
  $pan_no = $_REQUEST['pan_no'];
  $inv_no = $_REQUEST['inv_no'];
  $email_date = $_REQUEST['email_date'];
  $po_no = $_REQUEST['po_no'];
  $po_email_date = $_REQUEST['po_email_date'];
  $gst_per = $_REQUEST['gst_per'];
  $cgst = $_REQUEST['cgst'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $final_amount = $_REQUEST['final_amount'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];
  $dis_type = $_REQUEST['dis_type'];




  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . $db->escape_string($_REQUEST['description_' . $site_id])  . $col_sep . $_REQUEST['hsn_sac_' . $site_id] . $col_sep . $db->escape_string($_REQUEST['size_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['rate_' . $site_id] . $col_sep . $_REQUEST['amount_' . $site_id] . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep .floatval($_REQUEST['final_comm_' . $site_id]). $col_sep.floatval($_REQUEST['total_comm_' . $site_id]). $col_sep. $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] .$col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);

  $query = "CALL generate_manual_invoice_proc(
	  '{$client}','{$db->escape_string($b_name)}', '{$db->escape_string($b_address)}', '{$db->escape_string($gstin)}', '{$db->escape_string($pan_no)}', 
	  '{$db->escape_string($inv_no)}', '{$email_date}','{$db->escape_string($po_no)}', '{$po_email_date}', '{$gst_per}', '{$db->escape_string($cgst)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}','{$dis_type}', '{$dis_amt_per}','{$final_amount}','{$bank_acc_no}'
	);";

  //echo $query;die;  
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);

  // echo "<pre>";
  // print_r($return_data);
  // die;
  echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_MANUAL_ESTIMATE") {
  //echo json_encode($_REQUEST); exit();
  //$booking_id = intval($_REQUEST['booking_id']);
  $client = $_REQUEST['client'];
  $b_name = $_REQUEST['b_name'];
  $b_address = $_REQUEST['b_address'];
  $gstin = $_REQUEST['gstin'];
  $pan_no = $_REQUEST['pan_no'];
  $inv_no = $_REQUEST['inv_no'];
  $email_date = $_REQUEST['email_date'];
  $po_no = $_REQUEST['po_no'];
  $po_email_date = $_REQUEST['po_email_date'];
  $gst_per = $_REQUEST['gst_per'];
  $cgst = $_REQUEST['cgst'];
  $dis_amt_per = $_REQUEST['dis_amt_per'];
  $final_amount = $_REQUEST['final_amount'];
  $bank_acc_no = $_REQUEST['bank_acc_no'];
  $dis_type = $_REQUEST['dis_type'];




  $selected_site = $_REQUEST['selected_site'];
  $col_sep = '|#|';
  $row_sep = '[$]';
  $qry_string = "";
  foreach ($selected_site as $site_id) {
    $qry_string .= intval($site_id) . $col_sep . $db->escape_string($_REQUEST['description_' . $site_id])  . $col_sep . $_REQUEST['hsn_sac_' . $site_id] . $col_sep . $db->escape_string($_REQUEST['size_' . $site_id]) . $col_sep . $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['invoice_to_' . $site_id] . $col_sep . $_REQUEST['rate_' . $site_id] . $col_sep . $_REQUEST['amount_' . $site_id] . $row_sep;
    // $qry_string.= intval($site_id) . $col_sep . floatval($_REQUEST['rate_' . $site_id])  . $col_sep . floatval($_REQUEST['sqft_' . $site_id]). $col_sep .floatval($_REQUEST['final_comm_' . $site_id]). $col_sep.floatval($_REQUEST['total_comm_' . $site_id]). $col_sep. $_REQUEST['invoice_from_' . $site_id] . $col_sep . $_REQUEST['site_psudo_name_' . $site_id] .$col_sep . $_REQUEST['bkp_' . $site_id] . $row_sep;
  }
  $qry_string = rtrim($qry_string, $row_sep);

  $query = "CALL generate_manual_invoice_estimate_proc(
	  '{$client}','{$db->escape_string($b_name)}', '{$db->escape_string($b_address)}', '{$db->escape_string($gstin)}', '{$db->escape_string($pan_no)}', 
	  '{$db->escape_string($inv_no)}', '{$email_date}','{$db->escape_string($po_no)}', '{$po_email_date}', '{$gst_per}', '{$db->escape_string($cgst)}', 
	  '{$qry_string}', '{$col_sep}', '{$row_sep}', '{$logged_user_id}','{$dis_type}', '{$dis_amt_per}','{$final_amount}','{$bank_acc_no}'
	);";

  // echo $query;die;  
  //echo json_encode(array('query' => $query)); exit();
  $rs = $db->query($query);
  $result = mysqli_fetch_assoc($rs);
  $return_data = array('result' => $result, 'query' => $query);
  echo json_encode($return_data);
}



//for printing mounting manual end here

elseif ($action_type == "SELECT_INVOICE") {
  $query = "SELECT im.invoice_id,im.invoice_no,ifnull(date_format(im.eff_start_date,'%d-%m-%Y'),'') eff_start_date,ifnull(date_format(im.eff_end_date,'%d-%m-%Y'),'') eff_end_date FROM invoice_master im WHERE im.booking_id='" . $booking_id . "'";
  $result = $db->query($query);
  $row = mysqli_fetch_assoc($result);
  $return_data = array('status' => true, 'invoice_value' => $row);
  echo json_encode($return_data);
} elseif ($action_type == "FINAL_INVOICE") {
  //$total_amt=$_REQUEST['total'];
  //echo $total_amt; die;
  $manual_invoice_no = trim($_REQUEST['manual_invoice_no']);
  $query = "INSERT INTO ohh_invoice(booking_id,inv_no,inv_date,tot_amt,dis_amt,aft_dis_amt,cgst,sgst,igst,pay_amt,created_by,created_ts,po_no,po_date,dis_pst,state)
	VALUES
  ('" . $booking_id . "', ";
  if (empty($manual_invoice_no)) {
    $query .= "generate_invoice_no_fun('R'), ";
  } else {
    $query .= "'" . $db->escape_string($manual_invoice_no) . "', ";
  }
  $query .= " STR_TO_DATE('" . $book_date . "','%d/%m/%Y'), '" . $total . "', '" . $discount_amt . "', '" . $final_amount . "', '" . $cgst . "', '" . $sgst . "', '" . $igst . "', '" . $tot_amt . "', '" . $logged_user_id . "', now(), '" . $po_no . "', STR_TO_DATE('" . $billing_from_2 . "','%d/%m/%Y'), '" . $dis_pst . "', '" . $state . "')";

  $result = $db->query($query);
  $row = mysqli_fetch_assoc($result);
  $return_data = array('status' => true, "query" => $query);
  echo json_encode($return_data);
} elseif ($action_type == "CANCEL_INVOICE") {
  $invoice_id = $_REQUEST['invoice_id'];
  $invoice_type = $_REQUEST['invoice_type'];

  if ($invoice_type == 'Booking Invoice') {
    $check_qry = "SELECT COUNT(1) AS cnt FROM receive_detail WHERE invoice_id = '" . $invoice_id . "' AND invoice_type = 'B'";
    $update_query = "UPDATE ooh_invoice_header SET status='C' WHERE invoice_id='" . $invoice_id . "'";
    //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();
  } elseif ($invoice_type == 'Mounting Invoice') {
    $check_qry = "SELECT COUNT(1) AS cnt FROM receive_detail WHERE invoice_id = '" . $invoice_id . "' AND invoice_type = 'M'";
    $update_query = "UPDATE mounting_invoice_header SET cancel_status='C' WHERE invoice_header_id='" . $invoice_id . "'";
  } elseif ($invoice_type == 'Printing Invoice') {
    $check_qry = "SELECT COUNT(1) AS cnt FROM receive_detail WHERE invoice_id = '" . $invoice_id . "' AND invoice_type = 'P'";
    $update_query = "UPDATE printing_invoice_header SET cancel_status='C' WHERE invoice_header_id='" . $invoice_id . "'";
  }
  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }
  $res = $db->query($update_query);
  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
} elseif ($action_type == "CANCEL_WORK_ORDER") {
  $mounting_id = $_REQUEST['mounting_id'];
  //$invoice_type = $_REQUEST['invoice_type'];
  //echo $mounting_id; die;

  //if($invoice_type == 'Booking Invoice') {
  //	$check_qry = "SELECT COUNT(1) AS cnt FROM received_detail WHERE invoice_id = '".$mounting_id."' AND invoice_type = 'B'";
  $update_query = "UPDATE mounting_header SET mounting_status='0' WHERE mounting_id='" . $mounting_id . "'";
  //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();
  //}




  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }

  $res = $db->query($update_query);

  //echo $update_query; 
  //echo $mounting_id;die;   

  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
} elseif ($action_type == "CANCEL_TRADING_WORK_ORDER") {
  $trade_id = $_REQUEST['trade_id'];
  //$invoice_type = $_REQUEST['invoice_type'];
  //echo $mounting_id; die;

  //if($invoice_type == 'Booking Invoice') {
  //	$check_qry = "SELECT COUNT(1) AS cnt FROM received_detail WHERE invoice_id = '".$mounting_id."' AND invoice_type = 'B'";
  $update_query = "UPDATE trading_po_header SET trading_status='0' WHERE trade_id='" . $trade_id . "'";
  //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();
  //}




  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }

  $res = $db->query($update_query);

  //echo $update_query; 
  //echo $mounting_id;die;   

  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
} elseif ($action_type == "CANCEL_PRINTING_WORK_ORDER") {
  $printing_id = $_REQUEST['printing_id'];
  //$invoice_type = $_REQUEST['invoice_type'];
  //echo $mounting_id; die;

  //if($invoice_type == 'Booking Invoice') {
  //	$check_qry = "SELECT COUNT(1) AS cnt FROM received_detail WHERE invoice_id = '".$mounting_id."' AND invoice_type = 'B'";
  $update_query = "UPDATE printing_header SET printing_status='0' WHERE printing_id='" . $printing_id . "'";
  //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();
  //}




  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }

  $res = $db->query($update_query);

  //echo $update_query; 
  //echo $mounting_id;die;   

  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
} elseif ($action_type == "HSN_SAC") {
  $query = "SELECT DISTINCT h.hsn_sac_code, my_Split_function(get_gst_func(h.hsn_sac_id), '|', 4) gst_percent
	FROM hsn_sac_master h
	INNER JOIN tax_hsn_mapping m ON h.hsn_sac_id = m.hsn_sac_id
	WHERE h.is_active = 1;";
  $result = $db->query($query);
  while ($data = mysqli_fetch_assoc($result)) {
    $ret[] = $data;
  }
  $return_data = array('status' => true, 'hsn_list' => $ret);
  echo json_encode($return_data);
} elseif ($action_type == "BANK_ACC") {
  $get_booking_id = $_REQUEST['booking_id'];
  // 	$query = "SELECT bank_account_id, account_no FROM bank_account_master bam
  // INNER JOIN company_master cm ON cm.company_id = bam.company_id
  // INNER JOIN booking_header bh ON bh.company_id = cm.company_id
  // WHERE bh.booking_id ='".$get_booking_id."' AND bam.is_active = 1";
  $query = "SELECT bank_account_id,CONCAT(bm.bank_name,'-',account_no) as account_no FROM bank_account_master bam
INNER JOIN bank_master bm ON bm.bank_id = bam.bank_id 
WHERE  bam.is_active = 1";

  $result = $db->query($query);
  while ($data = mysqli_fetch_assoc($result)) {
    $ret[] = $data;
  }
  $return_data = array('status' => true, 'acc_list' => $ret);
  echo json_encode($return_data);
}

// elseif($action_type=="BANK_ACC_LIST"){ 
// 	//$get_booking_id = $_REQUEST['booking_id'];
// 	$query = "SELECT bank_account_id,CONCAT(bm.bank_name,'-',account_no) as account_no FROM bank_account_master bam
// INNER JOIN bank_master bm ON bm.bank_id = bam.bank_id 
// WHERE  bam.is_active = 1";
// 	$result = $db->query($query);
// 	while($data=mysqli_fetch_assoc($result)) {
// 		$ret[] = $data;
// 	}
// 	$return_data = array('status' => true, 'acc_list' => $ret);
// 	echo json_encode($return_data);
// }

elseif ($action_type == "CANCEL_INVOICE_FOR_MANUAL") {
  $invoice_id = $_REQUEST['invoice_id'];

  $check_qry = "SELECT COUNT(1) AS cnt FROM received_detail WHERE invoice_id = '" . $invoice_id . "' AND invoice_type = 'MU'";
  $update_query = "UPDATE manual_invoice_header SET cancel_status='C' WHERE invoice_header_id='" . $invoice_id . "'";
  //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();


  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }
  $res = $db->query($update_query);
  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
} elseif ($action_type == "CANCEL_INVOICE_FOR_MANUAL_ESTIMATE") {
  $invoice_id = $_REQUEST['invoice_id'];

  $check_qry = "SELECT COUNT(1) AS cnt FROM received_detail WHERE invoice_id = '" . $invoice_id . "'";
  $update_query = "UPDATE manual_estimate_header SET cancel_status='C' WHERE estimate_header_id='" . $invoice_id . "'";
  //echo json_encode(array('check_qry' => $check_qry, 'update_query' => $update_query)); exit();


  $rs_check_cnt = $db->query($check_qry);
  $check_cnt = mysqli_fetch_assoc($rs_check_cnt);
  if ($check_cnt['cnt'] > 0) {
    $return_data  = array('status' => false, 'msg' => "This invoice cannot be cancelled. Payment already made.");
    echo json_encode($return_data);
    exit();
  }
  $res = $db->query($update_query);
  if ($res) {
    $msg = "Invoice Cancelled sucessfully !";
  } else {
    $msg = "Try again !";
  }

  $return_data  = array('status' => true, 'msg' => $msg);
  echo json_encode($return_data);
}
