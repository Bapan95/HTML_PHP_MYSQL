<?php 
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');

$logged_user_id = my_session('user_id');
if(isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')){
	$logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$return_data  = array();


if($action_type=="BOOKING_INFO")
{
	$booking_id=$_REQUEST['booking_id'];
	$query = "SELECT bh.booking_no,bh.remarks,date_format(bh.booking_from,'%d/%m/%Y') booking_from, date_format(bh.booking_to,'%d/%m/%Y') booking_to,date_format(bh.billing_from,'%d/%m/%Y') billing_from,date_format(bh.billing_to,'%d/%m/%Y') billing_to,get_future_or_curdate_func(bh.booking_from) AS return_date,bh.rent_applicable_flag,c.client_name,bd.extension_prospect,cm.company_name FROM booking_header bh
	INNER JOIN booking_detail bd ON bd.booking_id=bh.booking_id
	INNER JOIN client_master c ON c.client_id=bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE bh.booking_id=".$booking_id." group by bh.booking_no";
 
 $result = $db->query($query);
$data=mysqli_fetch_assoc($result);
  
$return_data  = array('status' => true,'qry'=>$query,'booking_info'=>$data);
echo json_encode($return_data);
}

elseif($action_type=="CANCEL_BOOKING"){
	$booking_id = intval($_REQUEST['booking_id']);
	$cancel_type = $_REQUEST['cancel_type'];
	$effective_on = $_REQUEST['effective_on'];
	//echo json_encode($_REQUEST); exit();
	
	$mail_file_name = '';
	$mail_file = $_FILES['mail_file']['name'];
	if(!empty($mail_file)){
		$file_ext = strtolower(end(explode(".", $mail_file)));
		//$allowed_ext = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx");
		$allowed_ext = array("jpg", "jpeg", "png", "pdf");
		if(in_array($file_ext, $allowed_ext)){
			$file_name = 'mail_file_' . time() . '.' . $file_ext;
			$sourcePath = $_FILES['mail_file']['tmp_name'];  
			$targetPath = "../upload_file/".$file_name;  
			if(move_uploaded_file($sourcePath, $targetPath)){  
				$mail_file_name = $file_name;
			}
		}
	}
	
	if($cancel_type == 'F'){
		if($effective_on == 'A'){
			$query = "UPDATE booking_header SET 
							cancel_remarks='".$_REQUEST['remarks']."',
							cancel_mail_file='".$mail_file_name."',
							cancel_by=".$logged_user_id.",
							cancel_status=b'1',
							cancel_ts=NOW() 
							WHERE booking_id=".$booking_id."";
		}
		else{
			$selected_site = implode(',', $_REQUEST['selected_site']);
			$query = "CALL delete_site_from_booking_proc({$booking_id}, {$logged_user_id}, '".$db->escape_string($_REQUEST['remarks'])."', '{$mail_file_name}', '{$selected_site}');";
		}
	}
	else{
		if($effective_on == 'A'){
			$query = "UPDATE booking_header SET 
							booking_from=str_to_date('".$_REQUEST['booking_from']."','%d/%m/%Y'),
							booking_to=str_to_date('".$_REQUEST['booking_upto']."','%d/%m/%Y'),
							billing_from=str_to_date('".$_REQUEST['billing_frm']."','%d/%m/%Y'),
							billing_to=str_to_date('".$_REQUEST['billing_upto']."','%d/%m/%Y'),
							partial_remarks='".$_REQUEST['remarks']."',
							cancel_mail_file='".$mail_file_name."',
							updated_by=".$logged_user_id.",
							updated_ts=NOW()	
							WHERE booking_id=".$booking_id."";
		}
		else{
			$selected_site = implode(',', $_REQUEST['selected_site']);
			$query = "CALL cancel_booked_site_proc({$booking_id}, '".$_REQUEST['booking_from']."', '".$_REQUEST['booking_upto']."', '".$_REQUEST['billing_frm']."', '".$_REQUEST['billing_upto']."', '".$_REQUEST['remarks']."', '{$mail_file_name}', {$logged_user_id}, '{$selected_site}');";
		}
	}
	//echo json_encode(array('query' => $query)); exit();
	$result = $db->query($query);
	if($result){
		$return_data  = array('status' => true, 'qry' => $query);
		if($cancel_type == 'P' && $effective_on == 'S'){
			$qry_result = mysqli_fetch_assoc($result);
			if($qry_result['booking_no'] != ''){
				$return_data['booking_no'] = $qry_result['booking_no'];
			}
			else{
				$return_data['status']  = false;
			}
		}
	}
	else{
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}
elseif ($action_type=="CANCEL_LISTING") {
 $from_date = $_REQUEST['from_date'];	
$to_date = $_REQUEST['to_date'];	
 $from_date =date('Y-m-d', strtotime($from_date));
  $to_date =date('Y-m-d', strtotime($to_date));

  $query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks, IFNULL(bh.cancel_mail_file, '') cancel_mail_file, 
'full' cancel_type 
FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id 
WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1  
UNION ALL
SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks, IFNULL(cl.cancel_mail_file, '') cancel_mail_file, 
'partial' cancel_type 
FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) ";
$result = $db->query($query);
while($data=mysqli_fetch_assoc($result)) {
	if($data['cancel_mail_file'] != ''){
		if(!file_exists(APATH . 'upload_file/' . $data['cancel_mail_file'])){
			$data['cancel_mail_file'] = '';
		}
	}
	$ret[] = $data;
}
$return_data  = array('status' => true,'qry'=>$query,'cancel_list'=>$ret);
echo json_encode($return_data);


}

if($action_type=="SEARCH_LIST")
{
	 $from_date = $_REQUEST['from_date'];	
$to_date = $_REQUEST['to_date'];	
 $from_date =date('Y-m-d', strtotime($from_date));
  $to_date =date('Y-m-d', strtotime($to_date));
$client = $_REQUEST['client'];
$user = $_REQUEST['user'];
$today=date('Y-m-d');
if(empty($user) && empty($client))	
{
   $query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks, 
'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id 
WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1  
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks, 
'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) ";

}
if(empty($user) && !empty($client))
{
	$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.client_id='".$client ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.client_id='".$client ."'"; 
}
elseif(empty($client) && !empty($user))
{
$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.created_by='".$user ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.created_by='".$user ."'"; 
}
if(!empty($user) && !empty($client))
{
	$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.created_by='".$user ."' AND bh.client_id='".$client ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.created_by='".$user ."' AND cl.client_id='".$client ."'"; 
}
 $result = $db->query($query);
while($data=mysqli_fetch_assoc($result))
  {
    $ret[] = $data;

  }
$return_data  = array('status' => true, 'qry'=>$query, 'search_list'=>$ret);
echo json_encode($return_data);

}



// elseif ($action_type=="DOWNLOAD_EXCEL") {
//  $from_date = $_REQUEST['from_date'];	
// $to_date = $_REQUEST['to_date'];	
//  $from_date =date('Y-m-d', strtotime($from_date));
//   $to_date =date('Y-m-d', strtotime($to_date));	
// $today = date('y-m-d');
// $query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
// date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
// date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
// date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
// INNER JOIN client_master c ON c.client_id=bh.client_id 
// WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1  
// UNION ALL
//  SELECT cl.booking_id,cl.booking_no,
// date_format(cl.created_ts,'%d-%m-%Y') booked_date,
// date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
// date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
// date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
// INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) "; 
//  $result = $db->query($query);

// $objPHPExcel = new PHPExcel();
// $objPHPExcel->setActiveSheetIndex(0);


// 		$objPHPExcel->getActiveSheet()->setCellValue('A1','Sl No.');
// 		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Booking Number');
// 		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Client Name');
// 		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Booking From');
// 		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Booking To');
// 		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Billing From');
// 		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Billing To');
// 		$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Booked Date');
// 		$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Cancel Date');
//         $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Remarks');
//         $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Cancel Type');

//    $styleArray = array(
// 		'font'  => array(
//         'bold'  => true,
//         'color' => array('rgb' => '000000'),   

//     	));
//     	$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);
//   foreach(range('A','K') as $columnID)
// 	{
// 		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
// 	}
// $rowCount = 2;
// $rowCount_new = 1;
// $existtempinid = array();
// while($row = mysqli_fetch_assoc($result))  
// {
// $objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


// $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $rowCount_new++); 
// $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $row['booking_no']);
// $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $row['client_name']);
// $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $row['booking_from']);
// $objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $row['booking_to']);
// $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $row['billing_from']);
// $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $row['billing_to']);
// $objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $row['booked_date']);
// $objPHPExcel->getActiveSheet()->setCellValue('I'.$rowCount, $row['cancel_ts']);
// $objPHPExcel->getActiveSheet()->setCellValue('J'.$rowCount, $row['cancel_remarks']);
// $objPHPExcel->getActiveSheet()->setCellValue('K'.$rowCount, $row['cancel_type']);
// $rowCount++;
// }	 
//   $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// 	ob_start();
// 	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// 	header('Content-Disposition: attachment;filename="'."disbursed_report".date('jS-F-y H-i-s').".xlsx".'"');
// 	header('Cache-Control: max-age=0');
// 	$objWriter->save("php://output");
// 	$xlsData = ob_get_contents();
// 	ob_end_clean();

// $file_name= 'CANCEL_LIST'.$today;
// $return_data =  array(
//         'status' => true,'file_name'=>$file_name,
//         'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
//     );
// echo json_encode($return_data);	exit;


// }


elseif ($action_type=="DOWNLOAD_EXCEL_SEARCH") 
{
  $from_date = $_REQUEST['from_date'];	
$to_date = $_REQUEST['to_date'];	
 $from_date =date('Y-m-d', strtotime($from_date));
  $to_date =date('Y-m-d', strtotime($to_date));
$client = $_REQUEST['client'];
$user = $_REQUEST['user'];
$today=date('Y-m-d');
 if($client!="")
   {
   	$qry1 = "SELECT client_name FROM client_master WHERE client_id='".$client."'";
   	$res1 = $db->query($qry1);
   	$da1 = mysqli_fetch_assoc($res1);
   	$cm_name = $da1['client_name'];
   }
   else
   {
   	$cm_name = "";
   }
   if($user!="")
   {
   	$qry2 = "SELECT name FROM user_master WHERE user_id='".$user."'";
   	$res2 = $db->query($qry2);
   	$da2 = mysqli_fetch_assoc($res2);
   	$u_name = $da2['name'];
   }
   else
   {
   	$u_name = "";
   }
	 
if(empty($user) && empty($client))	
{
   $query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks, 
'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id 
WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1  
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks, 
'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) ";

}
if(empty($user) && !empty($client))
{
	$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.client_id='".$client ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.client_id='".$client ."'"; 
}
elseif(empty($client) && !empty($user))
{
$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.created_by='".$user ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.created_by='".$user ."'"; 
}
if(!empty($user) && !empty($client))
{
	$query = "SELECT bh.booking_id,bh.booking_no,date_format(bh.created_ts,'%d-%m-%Y') booked_date,
date_format(bh.booking_from,'%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,
date_format(bh.billing_from,'%d-%m-%Y') billing_from,date_format(bh.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(bh.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(bh.cancel_remarks,'') as cancel_remarks,'full' cancel_type FROM booking_header bh 
INNER JOIN client_master c ON c.client_id=bh.client_id WHERE (bh.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND bh.cancel_status=1 AND bh.created_by='".$user ."' AND bh.client_id='".$client ."' 
UNION ALL
 SELECT cl.booking_id,cl.booking_no,
date_format(cl.created_ts,'%d-%m-%Y') booked_date,
date_format(cl.booking_from,'%d-%m-%Y') booking_from, date_format(cl.booking_to,'%d-%m-%Y') booking_to,
date_format(cl.billing_from,'%d-%m-%Y') billing_from,date_format(cl.cancel_ts,'%d-%m-%Y') cancel_ts,
date_format(cl.billing_to,'%d-%m-%Y') billing_to,c.client_name,ifnull(cl.cancel_remarks,'') as cancel_remarks,'partial' cancel_type FROM cancel_history cl 
INNER JOIN client_master c ON c.client_id=cl.client_id WHERE (cl.cancel_ts BETWEEN '".$from_date."' AND DATE_ADD('".$to_date."', INTERVAL 1 DAY)) AND cl.created_by='".$user ."' AND cl.client_id='".$client ."'"; 
}

 $result = $db->query($query);

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);


		$objPHPExcel->getActiveSheet()->setCellValue('A1','Sl No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Booking Number');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Client Name');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Booking From');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Booking To');
		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Billing From');
		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Billing To');
		$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Booked Date');
		$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Cancel Date');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Remarks');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Cancel Type');

   $styleArray = array(
		'font'  => array(
        'bold'  => true,
        'color' => array('rgb' => '000000'),   

    	));
    	$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);
  foreach(range('A','K') as $columnID)
	{
		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
	}
$rowCount = 2;
$rowCount_new = 1;
$existtempinid = array();
while($row = mysqli_fetch_assoc($result))  
{
$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


$objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $rowCount_new++); 
$objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, $row['booking_no']);
$objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount, $row['client_name']);
$objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount, $row['booking_from']);
$objPHPExcel->getActiveSheet()->setCellValue('E'.$rowCount, $row['booking_to']);
$objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $row['billing_from']);
$objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $row['billing_to']);
$objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $row['booked_date']);
$objPHPExcel->getActiveSheet()->setCellValue('I'.$rowCount, $row['cancel_ts']);
$objPHPExcel->getActiveSheet()->setCellValue('J'.$rowCount, $row['cancel_remarks']);
$objPHPExcel->getActiveSheet()->setCellValue('K'.$rowCount, $row['cancel_type']);
$rowCount++;
}	 
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	ob_start();
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'."disbursed_report".date('jS-F-y H-i-s').".xlsx".'"');
	header('Cache-Control: max-age=0');
	$objWriter->save("php://output");
	$xlsData = ob_get_contents();
	ob_end_clean();

$file_name= 'CANCEL_LIST_BY'.$u_name.'|'.$cm_name;
$return_data =  array(
        'status' => true,'file_name'=>$file_name,
        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
    );
echo json_encode($return_data);	exit;

}
/*elseif($action_type == "DELETE_SITE_FROM_BOOKING") {
	$booking_id = intval($_REQUEST['booking_id']);
	//echo json_encode($_REQUEST['selected_site']); exit();
	$selected_site = implode(',', $_REQUEST['selected_site']);
	$query = "CALL delete_site_from_booking_proc({$booking_id}, {$logged_user_id}, '{$selected_site}');";
	$result = $db->query($query);
	$qry_result = mysqli_fetch_assoc($result);
	if($qry_result['r_status'] == 1){
		$return_data  = array('status' => true, 'qry' => $query);
	}
	else{
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}*/
elseif($action_type == "GET_BOOKED_SITES") {
	$booking_id = intval($_REQUEST['booking_id']);
	$query = "SELECT bd.booking_detail_id, bd.site_id, 
	s.site_code, s.site_name, sm.state_name, rg.region_name, dt.district_name, zn.zone_name, ln.location_name, st.site_type_name, mv.media_vh_name, lt.light_type_name, s.width, s.height, s.sqft, s.face_side, s.primary_pic, s.longshot_pic, s.closeup_pic, s.present_pic, 
	get_booked_portion(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr) AS booked_portion, get_booked_sqft(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr, s.sqft) AS booked_sqft, bd.extension_prospect, bd.booking_amount 
	FROM booking_detail bd
	INNER JOIN site_master s ON bd.site_id = s.site_id
	INNER JOIN state_master sm ON s.state_id = sm.state_id 
	INNER JOIN region_master rg ON s.region_id = rg.region_id 
	INNER JOIN district_master dt ON s.district_id = dt.district_id 
	INNER JOIN zone_master zn ON s.zone_id = zn.zone_id 
	INNER JOIN location_master ln ON s.location_id = ln.location_id 
	INNER JOIN site_type_master st ON s.site_type_id = st.site_type_id 
	INNER JOIN media_vehicle mv ON s.media_vh_id = mv.media_vh_id 
	INNER JOIN light_type_master lt ON s.light_type_id = lt.light_type_id 
	WHERE bd.booking_id = {$booking_id};";
	$result = $db->query($query);
	$site_list = array();
	while($row = mysqli_fetch_assoc($result)){
		$site_list[] = $row;
	}
	$return_data  = array('status' => true, 'site_list' => $site_list, 'qry' => $query);
	echo json_encode($return_data);
}
?>