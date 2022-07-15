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


if ($action_type == "PAYMENT_LIST") {
	$vendor_id = $_REQUEST['vendor'];
	$ref_type = $_REQUEST['ref_type'];
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$today = date('d-m-Y');


	$query = "SELECT r.payment_number,date_format(r.payment_date,'%d/%m/%Y') payment_date,r.tds_amount,r.total_amount,r.remarks,ifnull(v.vendor_name, '') vendor_name FROM payment_header r 
	INNER JOIN vendor_master v ON r.vendor_id=v.vendor_id
	 WHERE (r.payment_date BETWEEN STR_TO_DATE('" . $from_date . "','%d/%m/%Y') AND  STR_TO_DATE('" . $to_date . "','%d/%m/%Y')) ";
	if (!empty($vendor_id)) {
		$query .= " AND r.vendor_id='" . $vendor_id . "'";
	}
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}

	if ($ref_type == "VIEW") {

		$return_data = array('status' => true, 'payment_list' => $ret, 'query' => $query);
		echo json_encode($return_data);
	}
	if ($ref_type == "DWNLD") {
		$objPHPExcel = new PHPExcel();
		$styleArray = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '000000')
			)
		);
		// Set the active Excel worksheet to sheet 0 
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Payment List: From ' . $from_date . ' To ' . $to_date);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
		// Initialise the Excel row number 

		$objPHPExcel->getActiveSheet()->setCellValue('A2', 'SL No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Payment Number');
		$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Vendor Name');
		$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Payment Date');
		$objPHPExcel->getActiveSheet()->setCellValue('E2', 'TDS Amount');
		$objPHPExcel->getActiveSheet()->setCellValue('F2', 'Total Amount');
		$objPHPExcel->getActiveSheet()->setCellValue('G2', 'Remarks');


		$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->applyFromArray($styleArray);

		foreach (range('A', 'G') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}

		$rowCount = 3;
		$rowCount_new = 1;
		$existtempinid = array();
		foreach ($ret as  $value) {


			$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $value['payment_number']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $value['vendor_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $value['payment_date']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $value['tds_amount']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $value['total_amount']);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCount, $value['remarks']);



			$rowCount++;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		ob_start();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . "disbursed_report" . date('jS-F-y H-i-s') . ".xlsx" . '"');
		header('Cache-Control: max-age=0');
		$objWriter->save("php://output");
		$xlsData = ob_get_contents();
		ob_end_clean();

		$file_name = 'payment_list' . $today;
		$return_data =  array(
			'status' => true, 'file_name' => $file_name,
			'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
		);
		echo json_encode($return_data);
		exit;
	}
} elseif ($action_type == "RECEIPT_LIST") {
	$vendor_id = $_REQUEST['client'];
	$ref_type = $_REQUEST['ref_type'];
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$today = date('d-m-Y');

	$query = "select rh.receipt_no,DATE_FORMAT(rh.payment_date,'%d-%m-%Y') as payment_date,ifnull(rh.date,'') date,
	c.client_name,rh.amount,ifnull(rh.tds_pct,'') tds_pct,ifnull(rh.tds_amt,'') tds_amt,ifnull(rh.payment_amt,'') payment_amt,
	rh.received_id from received_header rh
inner join client_master c on c.client_id=rh.client_id 
where  (rh.payment_date between STR_TO_DATE('" . $from_date . "','%d/%m/%Y') and STR_TO_DATE('" . $to_date . "','%d/%m/%Y'))";
	// echo $query;
	// die;
	if (!empty($vendor_id)) {
		$query .= " AND rh.client_id='" . $vendor_id . "'";
	}
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}

	if ($ref_type == "VIEW") {

		$return_data = array('status' => true, 'payment_list' => $ret, 'query' => $query);
		echo json_encode($return_data);
	}
	if ($ref_type == "DWNLD") {
		$objPHPExcel = new PHPExcel();
		$styleArray = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '000000')
			)
		);


		// Set the active Excel worksheet to sheet 0 
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Client Receipt Register: From ' . $from_date . ' To ' . $to_date);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
		// Initialise the Excel row number 

		$objPHPExcel->getActiveSheet()->setCellValue('A2', 'SL No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Receipt Number');
		$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Receipt Date');
		$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Receipt Date');
		$objPHPExcel->getActiveSheet()->setCellValue('E2', 'Client Name');
		$objPHPExcel->getActiveSheet()->setCellValue('F2', 'Total Amount');

		$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->applyFromArray($styleArray);

		foreach (range('A', 'F') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}

		$rowCount = 3;
		$rowCount_new = 1;
		$existtempinid = array();
		foreach ($ret as  $value) {

			$amount = (($amount * 1) + ($value['amount'] * 1)) * 1;
			$tds_pct = (($tds_pct * 1) + ($value['tds_pct'] * 1)) * 1;
			$tds_amt = (($tds_amt * 1) + ($value['tds_amt'] * 1)) * 1;
			$payment_amt = (($payment_amt * 1) + ($value['payment_amt'] * 1)) * 1;


			$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $value['receipt_no']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $value['payment_date']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $value['date']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $value['client_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $value['amount']);





			$rowCount++;
		}
		$rowCount++;
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, 'Total');
		$objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':D' . $rowCount);
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $amount);
		//$objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount, $tds_pct); 
		//$objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount, $tds_amt); 
		//$objPHPExcel->getActiveSheet()->setCellValue('H'.$rowCount, $payment_amt); 

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		ob_start();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . "disbursed_report" . date('jS-F-y H-i-s') . ".xlsx" . '"');
		header('Cache-Control: max-age=0');
		$objWriter->save("php://output");
		$xlsData = ob_get_contents();
		ob_end_clean();

		$file_name = 'client_receipt_register' . $today;
		$return_data =  array(
			'status' => true, 'file_name' => $file_name,
			'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
		);
		echo json_encode($return_data);
		exit;
	}
}
