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


if ($action_type == "DOWNLOAD_EXCEL_SEARCH_INV") {
    $from_date = $_REQUEST['from_date'];
    $to_date = $_REQUEST['to_date'];
    $from_date = date('Y-m-d', strtotime($from_date));
    $to_date = date('Y-m-d', strtotime($to_date));
    $client_id = $_REQUEST['client_id'];
    //echo $client_id; die; 
    // $inv = $_REQUEST['inv'];
    $today = date('Y-m-d');

    if (!empty($client_id)) {
        $condition = " AND cmt.client_id = " . $client_id . "";
    }
    $query = "SELECT 'Booking Invoice' as invoice, oih.invoice_no, date_format(oih.generated_date,'%d/%m/%Y') as generated_date,
				bh.booking_no,cm.company_name,cmt.client_name,
				ifnull(oih.gross_amount,'0.00') gross_amount,
				CASE
				WHEN ifnull(oih.discount_amount,0) != 0.00 THEN oih.discount_amount
				ELSE ((oih.gross_amount * oih.discount_percent) / 100) 
				END AS discount_amount,
				oih.net_amount,oih.cgst_percent,oih.cgst_amount,
				oih.sgst_percent,oih.sgst_amount,oih.igst_percent,oih.igst_amount,oih.payable_amount,
				ifnull(receipt_no,' ') receipt_no,ifnull(date_format(payment_date,'%d/%m/%Y'),'') payment_date ,ifnull(rd.adjusted_amount,0) as Paid_amount,ifnull(rd.tds_amt ,0.00) as tds_amt
				FROM ooh_invoice_header oih
				INNER JOIN booking_header bh ON bh.booking_id = oih.booking_id
				INNER JOIN company_master cm ON cm.company_id = bh.company_id
				INNER JOIN client_master cmt ON cmt.client_id = bh.client_id
				LEFT JOIN received_detail rd ON rd.invoice_id=oih.invoice_id and rd.invoice_type='B'
				left JOIN received_header rh ON rh.received_id = rd.received_id
				WHERE oih.inv_type ='T' AND oih.status = 'A' AND
				(oih.generated_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') " . $condition . "
				
				UNION ALL
				
				SELECT 'Mounting Invoice' as invoice,mih.invoice_no,date_format(mih.invoice_date,'%d/%m/%Y') as generated_date, bh.booking_no,cm.company_name,cmt.client_name,
				mih.gross_total as gross_amount,
				CASE
				WHEN ifnull(mih.total_discount,0) != 0.00 THEN mih.total_discount
				ELSE ifnull(((mih.gross_total * mih.discount_percent) / 100),0) 
				END AS total_discount,
				/*ifnull(mih.after_discount,0)*/ mih.gross_total as net_amount,mih.cgst_percentage  as cgst_percent, 
				 mih.total_cgst as cgst_amount, mih.sgst_percentage as sgst_percent,mih.total_sgst as sgst_amount, ifnull(mih.igst_percentage,'0.00') as igst_percent,
				 ifnull(mih.total_igst,'0.00') as igst_amount,mih.payable_amount as payable_amount,
				ifnull(receipt_no,' ') receipt_no,ifnull(date_format(payment_date,'%d/%m/%Y'),'') payment_date ,ifnull(rd.adjusted_amount,0) as Paid_amount,ifnull(rd.tds_amt,0.00) as tds_amt
				FROM mounting_invoice_header mih
				INNER JOIN booking_header bh ON bh.booking_id = mih.booking_id
				INNER JOIN company_master cm ON cm.company_id = bh.company_id
				INNER JOIN client_master cmt ON cmt.client_id = bh.client_id
				LEFT JOIN received_detail rd ON rd.invoice_id=mih.invoice_header_id and rd.invoice_type='M'
				left JOIN received_header rh ON rh.received_id = rd.received_id
				WHERE  mih.inv_type ='T' AND mih.cancel_status = 'A' AND
				(mih.invoice_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') " . $condition . "
				
				
				UNION ALL
				
				 
				SELECT 'Printing Invoice' as invoice,pih.invoice_no,date_format(pih.invoice_date,'%d/%m/%Y') as generated_date, bh.booking_no,cm.company_name,cmt.client_name,
				pih.gross_total as gross_amount,
				CASE
				WHEN ifnull(pih.total_discount,0) != 0.00 THEN pih.total_discount
				ELSE ((pih.gross_total * pih.discount_percent) / 100) 
				END AS total_discount,
				/*ifnull(pih.after_discount,'0.00')*/ pih.gross_total as net_amount,pih.cgst_percentage  as cgst_percent,
				 pih.total_cgst as cgst_amount, pih.sgst_percentage as sgst_percent,pih.total_sgst as sgst_amount,ifnull(pih.igst_percentage,'0.00') as igst_percent,
				 ifnull(pih.total_igst,'0.00') as igst_amount,pih.payable_amount as payable_amount,
				ifnull(receipt_no,' ') receipt_no,ifnull(date_format(payment_date,'%d/%m/%Y'),'') payment_date ,ifnull(rd.adjusted_amount,0) as Paid_amount,ifnull(rd.tds_amt,0.00) as tds_amt
				FROM printing_invoice_header pih
				INNER JOIN booking_header bh ON bh.booking_id = pih.booking_id
				INNER JOIN company_master cm ON cm.company_id = bh.company_id
				INNER JOIN client_master cmt ON cmt.client_id = bh.client_id
				LEFT JOIN received_detail rd ON rd.invoice_id=pih.invoice_header_id and rd.invoice_type='P'
				left JOIN received_header rh ON rh.received_id = rd.received_id
				WHERE  pih.inv_type ='T' AND pih.cancel_status = 'A' AND
				(pih.invoice_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') " . $condition . "
				
				
				UNION ALL
				
				
				SELECT 'Manual Invoice' as invoice,muh.invoice_no,date_format(muh.invoice_date,'%d/%m/%Y') invoice_date,'' as booking_no,'' as company_name,cmt.client_name,
				muh.gross_total as gross_amount,
				CASE
				WHEN ifnull(muh.discount_amount,0) != 0.00 THEN muh.discount_amount
				ELSE ((muh.gross_total * muh.discount_percent) / 100) 
				END AS discount_amount,
				ifnull(muh.after_discount,0) as net_amount,muh.cgst_percentage as cgst_percent,
				muh.total_cgst as cgst_amount, muh.sgst_percentage as sgst_percent,muh.total_sgst as sgst_amount,muh.igst_percentage as igst_percent,
				 muh.total_igst as igst_amount,muh.payable_amount as payable_amount,
				ifnull(receipt_no,' ') receipt_no,ifnull(date_format(payment_date,'%d/%m/%Y'),'') payment_date ,ifnull(rd.adjusted_amount,0) as Paid_amount,ifnull(rd.tds_amt,0.00) as tds_amt
				FROM manual_invoice_header muh
				INNER JOIN client_master cmt ON cmt.client_id = muh.client_id
				LEFT JOIN received_detail rd ON rd.invoice_id=muh.invoice_header_id and rd.invoice_type='MU'
				left JOIN received_header rh ON rh.received_id = rd.received_id
				WHERE  muh.cancel_status = 'A' AND
				(muh.invoice_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') " . $condition . "
				
				union all
				
				select 'Credit Note' as invoice,credit_note_no,date_format(credit_note_date,'%d/%m/%Y') crdt,' ' bkngno,'Signatures Advertising Private Limited' Company_Name,
				cmt.client_name,(gross_amount *-1) as gross_amount,0,(gross_amount *-1) as net_amount,cgst_percentage,(total_cgst *-1) as total_cgst,sgst_percentage,
				(total_sgst * -1) as total_sgst,igst_percentage,
				(total_igst * - 1) total_igst,(payable_amount * -1) as payable_amount,' ' rect,' ' rctdt, 0,0
				from credit_note_details crn
				INNER JOIN client_master cmt ON cmt.client_id = crn.client_id
				where credit_note_date BETWEEN '" . $from_date . "' AND '" . $to_date . "'  " . $condition . "
				
				ORDER BY 6,3";

    //echo $query; die;
    $result = $db->query($query);
    $objPHPExcel = new PHPExcel();
    $styleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000')
        )
    );
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sales Register: From ' . $from_date . ' To ' . $to_date);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');

    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Sl No.');
    $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Invoice No');
    $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Invoice Date');
    $objPHPExcel->getActiveSheet()->setCellValue('D2', 'Invoice Type');
    $objPHPExcel->getActiveSheet()->setCellValue('E2', 'Booking No');
    $objPHPExcel->getActiveSheet()->setCellValue('F2', 'Company Name');
    $objPHPExcel->getActiveSheet()->setCellValue('G2', 'Client Name');
    $objPHPExcel->getActiveSheet()->setCellValue('H2', 'Net Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('I2', 'Total Discount');
    $objPHPExcel->getActiveSheet()->setCellValue('J2', 'After Discount');
    $objPHPExcel->getActiveSheet()->setCellValue('K2', 'CGST Percentage');
    $objPHPExcel->getActiveSheet()->setCellValue('L2', 'CGST Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('M2', 'SGST Percentage');
    $objPHPExcel->getActiveSheet()->setCellValue('N2', 'SGST Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('O2', 'IGST Percentage');
    $objPHPExcel->getActiveSheet()->setCellValue('P2', 'IGST Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('Q2', ' Gross amount');

    $objPHPExcel->getActiveSheet()->setCellValue('R2', 'RECEIPT NO');
    $objPHPExcel->getActiveSheet()->setCellValue('S2', 'PAYMENT DATE');
    $objPHPExcel->getActiveSheet()->setCellValue('T2', 'PAID Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('U2', 'TDS Amount');

    $styleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000'),

        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('A1:U1')->applyFromArray($styleArray);
    foreach (range('A', 'U') as $columnID) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }
    $rowCount = 3;
    $rowCount_new = 1;
    $invoice_number = "";
    $existtempinid = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $gross_amount = (($gross_amount * 1) + ($row['gross_amount'] * 1)) * 1;
        $discount_amount = (($discount_amount * 1) + ($row['discount_amount'] * 1)) * 1;
        $net_amount = (($net_amount * 1) + ($row['net_amount'] * 1)) * 1;
        $cgst_amount = (($cgst_amount * 1) + ($row['cgst_amount'] * 1)) * 1;
        $sgst_amount = (($sgst_amount * 1) + ($row['sgst_amount'] * 1)) * 1;
        $igst_amount = (($igst_amount * 1) + ($row['igst_amount'] * 1)) * 1;
        $total_igst = (($total_igst * 1) + ($row['total_igst'] * 1)) * 1;
        $payable_amount = (($payable_amount * 1) + ($row['payable_amount'] * 1)) * 1;
        $Paid_amount = (($Paid_amount * 1) + ($row['Paid_amount'] * 1)) * 1;
        $tds_amt = (($tds_amt * 1) + ($row['tds_amt'] * 1)) * 1;


        $objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);

        if ($row['invoice_no'] != $invoice_number) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $row['invoice_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $row['generated_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $row['invoice']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $row['booking_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $row['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCount, $row['client_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCount, $row['gross_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCount, $row['discount_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCount, $row['net_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $rowCount, $row['cgst_percent']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $rowCount, $row['cgst_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $rowCount, $row['sgst_percent']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $rowCount, $row['sgst_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $rowCount, $row['igst_percent']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $rowCount, $row['igst_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rowCount, $row['payable_amount']);

            $objPHPExcel->getActiveSheet()->setCellValue('R' . $rowCount, $row['receipt_no']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $rowCount, $row['payment_date']);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $rowCount, $row['Paid_amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $rowCount, $row['tds_amt']);
            $rowCount++;
        } else {
        }
        $invoice_number = $row['invoice_no'];
    }
    $rowCount++;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, 'Total');
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':G' . $rowCount);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCount, $gross_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCount, $discount_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCount, $net_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $rowCount, $cgst_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $rowCount, $sgst_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $rowCount, $igst_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rowCount, $payable_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('T' . $rowCount, $Paid_amount);
    $objPHPExcel->getActiveSheet()->setCellValue('U' . $rowCount, $tds_amt);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    ob_start();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . "disbursed_report" . date('jS-F-y H-i-s') . ".xlsx" . '"');
    header('Cache-Control: max-age=0');
    $objWriter->save("php://output");
    $xlsData = ob_get_contents();
    ob_end_clean();

    $file_name = 'Sale_Register_' . $u_name . '|' . $cm_name;
    $return_data =  array(
        'status' => true, 'file_name' => $file_name,
        'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
    );
    echo json_encode($return_data);
    exit;
} else if ($action_type == "INV_REGISTER_DUPLICATE") {
    $from_date = $_REQUEST['from_date'];
    $to_date = $_REQUEST['to_date'];
    $from_date = date('Y-m-d', strtotime($from_date));
    $to_date = date('Y-m-d', strtotime($to_date));
    $client_id = $_REQUEST['client_id'];
    //echo $client_id; die; 
    // $inv = $_REQUEST['inv'];
    $today = date('Y-m-d');

    if (!empty($client_id)) {
        $condition = " AND cmt.client_id = " . $client_id . "";
    }
    $query = "SELECT 'Booking Invoice' as invoice,oih.invoice_id as booking_id, 'http://signature.flamingostech.com/ooh_bill.html' as link, oih.invoice_no, date_format(oih.generated_date,'%d/%m/%Y') as generated_date,
				bh.booking_no,cm.company_name,cmt.client_name,
				ifnull(oih.gross_amount,'0.00') gross_amount,
				CASE
				WHEN oih.discount_amount != 0.00 THEN oih.discount_amount
				ELSE ((oih.gross_amount * oih.discount_percent) / 100) 
				END AS discount_amount,
				oih.net_amount,oih.cgst_percent,oih.cgst_amount,
				oih.sgst_percent,oih.sgst_amount,oih.igst_percent,oih.igst_amount,oih.payable_amount,
				ifnull(receipt_no,' ') receipt_no,ifnull(date_format(payment_date,'%d/%m/%Y'),'') payment_date ,ifnull(rd.adjusted_amount,0) as Paid_amount,ifnull(rd.tds_amt ,0.00) as tds_amt
				FROM ooh_invoice_header oih
				INNER JOIN booking_header bh ON bh.booking_id = oih.booking_id
				INNER JOIN company_master cm ON cm.company_id = bh.company_id
				INNER JOIN client_master cmt ON cmt.client_id = bh.client_id
				LEFT JOIN received_detail rd ON rd.invoice_id=oih.invoice_id and rd.invoice_type='B'
				left JOIN received_header rh ON rh.received_id = rd.received_id
				WHERE oih.inv_type ='T' AND oih.status = 'A' AND
				(oih.generated_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') " . $condition . "
				
				
				
				ORDER BY 6,3";


    // echo $query; die;
    $result = $db->query($query);
    while ($data = mysqli_fetch_assoc($result)) {
        $ret[] = $data;
    }
    $return_data  = array('status' => true, 'qry' => $query, 'search_list' => $ret);
    echo json_encode($return_data);
}
