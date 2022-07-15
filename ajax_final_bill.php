<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
/*
	use PhpOffice\PhpPresentation\Autoloader;
	use PhpOffice\Common\CommonAutoloader;
	use PhpOffice\PhpPresentation\IOFactory;
	use PhpOffice\PhpPresentation\Slide;
	use PhpOffice\PhpPresentation\PhpPresentation;
	use PhpOffice\PhpPresentation\AbstractShape;
	use PhpOffice\PhpPresentation\DocumentLayout;
	use PhpOffice\PhpPresentation\Shape\Drawing;
	use PhpOffice\PhpPresentation\Shape\Group;
	use PhpOffice\PhpPresentation\Shape\RichText;
	use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
	use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
	use PhpOffice\PhpPresentation\Style\Alignment;
	use PhpOffice\PhpPresentation\Style\Bullet;
	use PhpOffice\PhpPresentation\Style\Border;
	use PhpOffice\PhpPresentation\Style\Fill;
	use PhpOffice\PhpPresentation\Style\Color as StyleColor;
	use PhpOffice\PhpPresentation\Slide\Transition;
	use PhpOffice\PhpPresentation\Slide\Animation;
	use PhpOffice\PhpPresentation\Style\Color;
*/
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
if ($action_type == "availability_status") {
    $return_type = $_REQUEST['return_type'];
    $from_date = implode('-', array_reverse(explode('/', $_REQUEST['from_date'])));
    $to_date = implode('-', array_reverse(explode('/', $_REQUEST['to_date'])));

    $state = "";
    if (!empty($_REQUEST['state'])) {
        $state = implode(',', $_REQUEST['state']);
    }
    $region = "";
    if (!empty($_REQUEST['region'])) {
        $region = implode(',', $_REQUEST['region']);
    }
    $district = "";
    if (!empty($_REQUEST['district'])) {
        $district = implode(',', $_REQUEST['district']);
    }
    $zone = "";
    if (!empty($_REQUEST['zone'])) {
        $zone = implode(',', $_REQUEST['zone']);
    }
    $location = "";
    if (!empty($_REQUEST['location'])) {
        $location = implode(',', $_REQUEST['location']);
    }
    $site_type = "";
    if (!empty($_REQUEST['site_type'])) {
        $site_type = implode(',', $_REQUEST['site_type']);
    }
    $media_vehicle = "";
    if (!empty($_REQUEST['media_vehicle'])) {
        $media_vehicle = implode(',', $_REQUEST['media_vehicle']);
    }
    $type = "";
    if (!empty($_REQUEST['type'])) {
        $type = implode(',', $_REQUEST['type']);
    }

    $width = floatval($_REQUEST['width']);
    $height = floatval($_REQUEST['height']);
    $sqft = floatval($_REQUEST['sqft']);

    $searched_sites = $_REQUEST['searched_sites'];
    if (!empty($searched_sites)) {
        $searched_sites = implode(',', $searched_sites);
    } else {
        $searched_sites = "";
    }

    $query = "CALL get_available_sites_procedure(" . $logged_user_id . ", '" . $return_type . "', '" . $from_date . "', '" . $to_date . "', '" . $state . "', '" . $region . "', '" . $district . "', '" . $zone . "', '" . $location . "', '" . $site_type . "', '" . $media_vehicle . "', '" . $type . "', " . $width . ", " . $height . ", " . $sqft . ", '" . $searched_sites . "');";

    $result = $db->query($query);
    if ($return_type == "get_count") {
        $data = mysqli_fetch_assoc($result);
        $return_data  = array('status' => true, 'site_data' => $data/*, 'query' => $query*/);
    } else if ($return_type == "view_list") {
        $site_list = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $site_list[] = $data;
        }
        $return_data  = array('status' => true, 'site_list' => $site_list/*, 'query' => $query*/);
    } else if ($return_type == "download_excel") {

        $objPHPExcel = new PHPExcel();
        // Set the active Excel worksheet to sheet 0 
        $objPHPExcel->setActiveSheetIndex(0);
        // Initialise the Excel row number 

        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'SL No.');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Site Code');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Site/Hording Name');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'State');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Region');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'District');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Zone');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Location');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Site Type');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Media Type');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Width(ft)');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Height(ft)');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Face');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Unit');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Total Size(Sqft)');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Available Size(Sqft)');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Available Portion');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Type');
        $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Price/PM');

        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000')
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:S1')->applyFromArray($styleArray);

        foreach (range('A', 'S') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }
        //$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(false);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(60);

        //$objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setWrapText(true); 
        //start while loop to get data  
        $rowCount = 2;
        $rowCount_new = 1;
        $existtempinid = array();
        while ($row = mysqli_fetch_assoc($result)) { //print_r($row);exit;  
            $objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $row['site_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $row['site_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $row['state_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $row['region_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $row['district_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCount, $row['zone_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCount, $row['location_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCount, $row['site_type_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCount, $row['media_vh_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $rowCount, $row['width']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $rowCount, $row['height']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $rowCount, $row['face_side']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $rowCount, $row['site_qty']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $rowCount, $row['sqft']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $rowCount, $row['available_sqft']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $rowCount, $row['available_portion']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $rowCount, $row['light_type_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $rowCount, $row['display_charges']);

            $rowCount++;
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $file_name = APP_NAME . '_Availability_' . $from_date . '-to-' . $to_date;
        $excel_file = $file_name . '.xlsx';
        /*ob_start();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'."disbursed_report".date('jS-F-y H-i-s').".xlsx".'"');
		header('Cache-Control: max-age=0');
		$objWriter->save("php://output");
		$xlsData = ob_get_contents();
		ob_end_clean();*/
        //$objWriter->save(APATH . "available-sites/{$excel_file}");
        $objWriter->save("/tmp/{$excel_file}");

        $return_data =  array(
            'status' => true, 'file_name' => $excel_file/*,
			'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)*/
        );
    }

    echo json_encode($return_data);
    exit;
} elseif ($action_type == "final_bill") {

    $booking_id = intval($_REQUEST['booking_id']);

    $query_header = "SELECT date_format(oi.inv_date,'%d/%m/%Y') as inv_date,oi.inv_no,oi.cgst,oi.sgst,oi.tot_amt,oi.aft_dis_amt,oi.dis_pst,oi.dis_amt,cm.pan,cm.gstin,
	oi.pay_amt,oi.po_no,date_format(oi.po_date,'%d/%m/%Y') as po_date,oi.state,date_format(now(),'%d/%m/%Y') as now, 
	bh.booking_no,bh.remarks,get_booking_from_func(bh.booking_id) booking_from, 
	get_booking_to_func(bh.booking_id) booking_to, date_format(bh.billing_from,'%d/%m/%Y') billing_from, 
	date_format(bh.billing_to,'%d/%m/%Y') billing_to, is_extended_func(bh.booking_id) is_extended, bh.rent_applicable_flag, 
	bh.po_generated_flag, bh.mail_confirmation_flag, bh.created_by, date_format(bh.created_ts,'%d/%m/%Y') book_date, u.name,
	c.client_name, ifnull(c.address,'') AS client_address, bh.brand_name, bh.po_number, bh.mail_confirmation_date, cm.company_name, cm.company_shortname, cm.address,
	cm.village_town, cm.pin_code, c.state_id
	FROM booking_header bh
	INNER JOIN user_master u ON u.user_id = bh.created_by
	INNER JOIN client_master c ON c.client_id = bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	INNER JOIN ohh_invoice oi ON oi.booking_id = bh.booking_id
	WHERE bh.booking_id = " . $booking_id;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);
    //echo $query_header; die;
    $query_detail = "SELECT  bd.booking_amount,bh.booking_id, bd.site_id, bh.booking_no,bh.rent_applicable_flag, date_format(bh.booking_from, '%d/%m/%Y') booking_from, date_format(bh.booking_to, '%d/%m/%Y') booking_to, date_format(bh.billing_from, '%d/%m/%Y') billing_from, date_format(bh.billing_to, '%d/%m/%Y') billing_to, month_day_diff(bh.billing_from, bh.billing_to)  day_num,bh.package_amount,lt.light_type_name, s.site_name, s.site_code, l.location_name, m.media_vh_name,get_booked_portion(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr) AS booked_portion, bd.extension_prospect, bd.booking_amount, s.width, s.height, s.sqft, s.face_side, t.lookup_desc, st.site_type_name
	FROM booking_header bh  
	INNER JOIN booking_detail bd ON bd.booking_id = bh.booking_id
	INNER JOIN site_master s ON s.site_id = bd.site_id
	INNER JOIN location_master l ON l.location_id = s.location_id
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id
	LEFT JOIN lookup_table t ON t.lookup_id = bd.tenure_id
	WHERE bh.cancel_status = 0 AND (bh.booking_id = " . $booking_id . " OR bh.parent_booking_id = " . $booking_id . ") ORDER BY bh.booking_id, bd.site_id";

    /*$query_detail = "SELECT  bd.booked_portion,bd.sqft,bd.rate,bd.amount,bh.invoice_header_id, bd.site_id, bh.booking_id, s.site_name, s.site_code, l.location_name, m.media_vh_name,  s.width, s.height, s.sqft, s.face_side, st.site_type_name FROM mounting_invoice_header bh  
	INNER JOIN mounting_invoice_detail bd ON bd.invoice_header_id = bh.invoice_header_id
	INNER JOIN site_master s ON s.site_id = bd.site_id
	INNER JOIN location_master l ON l.location_id = s.location_id
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id
	WHERE bh.booking_id = 15  ORDER BY bh.booking_id, bd.site_id";*/
    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "ooh_bill") {

    $invoice_id = intval($_REQUEST['invoice_id']);
    // echo $invoice_id;die;
    $query_header = "SELECT ih.invoice_id, ih.invoice_no, ih.po_no, DATE_FORMAT(IFNULL(ih.email_date, '%d-%m-%Y'), '%d-%m-%Y') email_date, ih.booking_id, DATE_FORMAT(IFNULL(ih.generated_date, '%d-%m-%Y'), '%d-%m-%Y') invoice_date, DATE_FORMAT(ih.invoice_from, '%d-%m-%Y') invoice_from, DATE_FORMAT(ih.invoice_to, '%d/%m/%Y') invoice_to, ih.hsn_sac_no, ih.gross_amount, ih.net_amount, ih.cgst_percent, ih.cgst_amount, ih.sgst_percent, ih.sgst_amount, ih.igst_percent, ih.igst_amount, ih.payable_amount, ih.remarks, ih.status, cm.pan,  cm.gstin, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code, bh.booking_no, bh.brand_name, u.name, c.client_name, ifnull(c.address,'') AS client_address, IFNULL(st.state_code, 19) cust_state_code, c.pan cust_pan, c.gstin cust_gstin, ih.discount_percent, ih.discount_amount,ih.inv_type, bam.account_no, bam.ifsc_no, bam.branch_name, bm.bank_name, bam.branch_address 
	FROM ooh_invoice_header ih 
	LEFT JOIN booking_header bh ON ih.booking_id = bh.booking_id 
	LEFT JOIN user_master u ON u.user_id = bh.created_by 
	LEFT JOIN client_master c ON c.client_id = bh.client_id
	LEFT JOIN state_master st ON st.state_id = c.state_id
	LEFT JOIN company_master cm ON cm.company_id = bh.company_id
	LEFT JOIN bank_account_master bam ON bam.bank_account_id = ih.bank_account_id
	LEFT JOIN bank_master bm ON bm.bank_id = bam.bank_id
	WHERE ih.invoice_id = '" . $invoice_id . "'";

    // echo $query_header;die;
    $result_header = $db->query($query_header);

    $data_header = mysqli_fetch_assoc($result_header);
    // $invoice_id=$data_header['invoice_id'];
    // echo $invoice_id;die;
    if (!empty($data_header['branch_address'])) {
        $data_header['branch_address'] = nl2br($data_header['branch_address']);
    }


    $query_detail = "SELECT id.invoice_detail_id,id.site_id, s.site_code, s.site_name as s_name,id.site_psudo_name as site_name, lt.light_type_name, l.location_name, m.media_vh_name, s.width, s.height, s.sqft, s.face_side, st.site_type_name, id.rate, cal_actual_rent_func(id.rate, ih.invoice_from, ih.invoice_to, '', '') amount, IFNULL(id.quantity,1) quantity, id.invoice_from, id.invoice_to, id.final_rate ,id.site_psudo_name
	FROM ooh_invoice_detail id 
	INNER JOIN ooh_invoice_header ih ON ih.invoice_id = id.invoice_id 
	INNER JOIN site_master s ON s.site_id = id.site_id 
	INNER JOIN location_master l ON l.location_id = s.location_id 
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
	WHERE ih.invoice_id = '" . $invoice_id . "' 
	ORDER BY id.site_id;";

    /*$query_detail = "SELECT id.site_id, s.site_code, s.site_name, lt.light_type_name, l.location_name, m.media_vh_name, s.width, s.height, s.sqft, s.face_side, st.site_type_name, id.rate, cal_actual_rent_func(id.rate, ih.invoice_from, ih.invoice_to, '', '') amount, IFNULL(id.quantity,1) quantity 
	FROM ooh_invoice_detail id 
	INNER JOIN ooh_invoice_header ih ON ih.invoice_id = id.invoice_id 
	INNER JOIN site_master s ON s.site_id = id.site_id 
	INNER JOIN location_master l ON l.location_id = s.location_id 
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
	WHERE ih.invoice_id = '". $invoice_id ."' 
	ORDER BY id.site_id;";*/

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "GET_STAMP_SIGNATURE") {
    //$invoice_id = intval($_REQUEST['invoice_id']);
    //echo 'Partha';die;
    $query_1 = "SELECT IFNULL(upload_seal, '') upload_seal FROM company_master WHERE company_id = 2;";
    $result_1 = $db->query($query_1);
    $data_1 = mysqli_fetch_assoc($result_1);
    $stamp = "";
    if ($data_1['upload_seal'] != '') {
        $stamp = $data_1['upload_seal'];
    }

    $query_2 = "SELECT IFNULL(upload_signature, '') upload_signature FROM user_master WHERE user_id = '" . $logged_user_id . "';";
    $result_2 = $db->query($query_2);
    $data_2 = mysqli_fetch_assoc($result_2);
    $sign = "";
    if ($data_2['upload_signature'] != '') {
        $sign = $data_2['upload_signature'];
    }


    $return_data = array('stamp' => $stamp, 'sign' => $sign);
    echo json_encode($return_data);
} elseif ($action_type == "Contract_Invoice") {

    $contract_id = intval($_REQUEST['invoice_id']);


    $query_header = "SELECT ih.booking_id, ih.order_no, DATE_FORMAT(IFNULL(ih.order_date, '0000-00-00'), '%d/%m/%Y') order_date, cm.pan, cm.gstin, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code, bh.booking_no, bh.brand_name, u.name, c.client_name, ifnull(c.address,'') AS client_address, IFNULL(st.state_code, 19) cust_state_code, c.pan cust_pan, c.gstin cust_gstin,DATE_FORMAT(bh.booking_from, '%d/%m/%Y') booking_from,DATE_FORMAT(bh.billing_to, '%d/%m/%Y') billing_to,ih.contract_id
FROM contract_header ih 
LEFT JOIN booking_header bh ON ih.booking_id = bh.booking_id 
LEFT JOIN user_master u ON u.user_id = bh.created_by 
LEFT JOIN client_master c ON c.client_id = bh.client_id 
LEFT JOIN state_master st ON st.state_id = c.state_id 
LEFT JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE ih.booking_id = '" . $contract_id . "'";

    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);
    if (!empty($data_header['branch_address'])) {
        $data_header['branch_address'] = nl2br($data_header['branch_address']);
    }


    $query_detail = "SELECT id.site_id, s.site_code, s.site_name, lt.light_type_name, l.location_name, m.media_vh_name, s.width, s.height, s.sqft, s.face_side, st.site_type_name, id.rate, IFNULL(id.quantity,1) quantity, id.invoice_from, id.invoice_to, id.final_rate ,id.site_psudo_name,id.p_rate,id.m_rate
	FROM contract_detail id 
	INNER JOIN contract_header ih ON ih.contract_id = id.contract_id 
	INNER JOIN site_master s ON s.site_id = id.site_id 
	INNER JOIN location_master l ON l.location_id = s.location_id 
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
	WHERE ih.booking_id = '" . $contract_id . "' 
	ORDER BY id.site_id;";

    //echo $$query_header; die;

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "RENT_RECEPT") {

    $rent_recept_id = intval($_REQUEST['rent_recept_id']);


    $query_header = "select lp.payment_number,date_format(lp.payment_date,'%d-%m-%Y') as payment_date,
  lp.net_amount,lp.transaction_no,ifnull(bm.bank_name,'') as bank_name,lp.payment_mode,
  lp.tds_amount,lp.total_amount,lp.payment_id,lp.transaction_no,date_format(lp.payment_date,'%d/%m/%Y') payment_date,lp.tds_amount,lp.land_lord_id,lm.land_lord_name,lm.address,sm.width,sm.height,lp.remarks 
  from landlord_payment lp
  LEFT JOIN bank_master bm on bm.bank_id =lp.bank_id
  LEFT JOIN land_lord_master lm ON lm.land_lord_id = lp.land_lord_id
  LEFT JOIN site_master sm ON sm.site_id = lp.site_id
  where lp.payment_id = '" . $rent_recept_id . "'";

    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);
    if (!empty($data_header['branch_address'])) {
        $data_header['branch_address'] = nl2br($data_header['branch_address']);
    }


    $query_detail = "SELECT id.site_id, s.site_code, s.site_name, lt.light_type_name, l.location_name, m.media_vh_name, s.width, s.height, s.sqft, s.face_side, st.site_type_name, id.rate, IFNULL(id.quantity,1) quantity, id.invoice_from, id.invoice_to, id.final_rate ,id.site_psudo_name,id.p_rate,id.m_rate
	FROM contract_detail id 
	INNER JOIN contract_header ih ON ih.contract_id = id.contract_id 
	INNER JOIN site_master s ON s.site_id = id.site_id 
	INNER JOIN location_master l ON l.location_id = s.location_id 
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
	WHERE ih.booking_id = '" . $contract_id . "' 
	ORDER BY id.site_id;";

    //echo $$query_header; die;

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "mounting_bill") {

    //$booking_id = intval($_REQUEST['booking_id']);
    $invoice_id = intval($_REQUEST['invoice_id']);


    $query_header = "SELECT date_format(oi.invoice_date,'%d/%m/%Y') as invoice_date,oi.invoice_no,oi.total_cgst,oi.total_sgst,oi.total_igst,oi.gross_total,cm.pan,cm.gstin,oi.discount_amount,oi.cgst_percentage,oi.sgst_percentage,oi.hsn_sac_no,oi.po_no,date_format(oi.email_date,'%d/%m/%Y') as email_date,oi.igst_percentage,oi.discount_percent,oi.net_amount,c.gstin cust_gstin,IFNULL(st.state_code, 19) cust_state_code, c.pan cust_pan,
	oi.payable_amount,date_format(now(),'%d/%m/%Y') as now, 
	bh.booking_no, u.name,
	c.client_name, ifnull(c.address,'') AS client_address, bh.brand_name, cm.company_name, cm.company_shortname, cm.address,
	cm.village_town, cm.pin_code, c.state_id,oi.inv_type, bam.account_no, bam.ifsc_no, bam.branch_name, bm.bank_name, bam.branch_address FROM booking_header bh
	INNER JOIN user_master u ON u.user_id = bh.created_by
	INNER JOIN client_master c ON c.client_id = bh.client_id
	LEFT JOIN state_master st ON st.state_id = c.state_id

	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	INNER JOIN mounting_invoice_header oi ON oi.booking_id = bh.booking_id
	LEFT JOIN bank_account_master bam ON bam.bank_account_id = oi.bank_account_id
	LEFT JOIN bank_master bm ON bm.bank_id = bam.bank_id
	WHERE oi.invoice_header_id = '" . $invoice_id . "'";
    //bh.booking_id ='". $booking_id ."'";

    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT  bd.invoice_details_id,bd.booked_portion,bh.booking_id, bd.site_id, bh.booking_id,lt.light_type_name, s.site_name, s.site_code, l.location_name, m.media_vh_name, bd.sqft,bd.rate,bd.amount,bd.tot_amount, s.width, s.height, s.sqft, s.face_side, st.site_type_name,date_format(bd.mounting_date,'%d/%m/%Y') as mounting_date,bd.site_psudo_name 
FROM mounting_invoice_header bh 
INNER JOIN mounting_invoice_detail bd ON bd.invoice_header_id = bh.invoice_header_id 
INNER JOIN site_master s ON s.site_id = bd.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id 
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
WHERE bh.invoice_header_id = '" . $invoice_id . "' ORDER BY bh.invoice_header_id, bd.site_id";

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;  


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "printing_bill") {

    $invoice_id = intval($_REQUEST['invoice_id']);

    $query_header = "SELECT date_format(oi.invoice_date,'%d-%m-%Y') as invoice_date,oi.invoice_no,oi.hsn_sac_no,oi.total_cgst,oi.total_sgst,oi.gross_total,cm.pan,cm.gstin,oi.discount_amount,oi.cgst_percentage,oi.sgst_percentage,oi.po_no,date_format(oi.email_date,'%d-%m-%Y') as email_date,oi.igst_percentage,oi.discount_percent,oi.net_amount,IFNULL(oi.campaign_name,'') as campaign_name,IFNULL(st.state_code, 19) cust_state_code, c.pan cust_pan,
	oi.payable_amount,date_format(now(),'%d/%m/%Y') as now, 
	bh.booking_no, u.name,c.gstin cust_gstin,
	c.client_name, ifnull(c.address,'') AS client_address, bh.brand_name, cm.company_name, cm.company_shortname, cm.address,
	cm.village_town, cm.pin_code, c.state_id,oi.inv_type, bam.account_no, bam.ifsc_no, bam.branch_name, bm.bank_name, bam.branch_address FROM booking_header bh
	INNER JOIN user_master u ON u.user_id = bh.created_by 
	INNER JOIN client_master c ON c.client_id = bh.client_id
	LEFT JOIN state_master st ON st.state_id = c.state_id
	
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	INNER JOIN printing_invoice_header oi ON oi.booking_id = bh.booking_id
	LEFT JOIN bank_account_master bam ON bam.bank_account_id = oi.bank_account_id
	LEFT JOIN bank_master bm ON bm.bank_id = bam.bank_id
	WHERE oi.invoice_header_id = '" . $invoice_id . "'";
    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT  bd.site_id,bd.invoice_details_id,bd.booked_portion,bh.booking_id, bd.site_id, bh.booking_id,lt.light_type_name, s.site_name as s_name,bd.site_psudo_name, s.site_code, l.location_name, m.media_vh_name, bd.sqft,bd.rate,bd.amount,bd.tot_amount, s.width, s.height, s.sqft, s.face_side, st.site_type_name ,DATE_FORMAT(bd.printing_date,'%d/%m/%Y') as printing_date,bd.site_psudo_name
FROM printing_invoice_header bh 
INNER JOIN printing_invoice_detail bd ON bd.invoice_header_id = bh.invoice_header_id 
INNER JOIN site_master s ON s.site_id = bd.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id 
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
WHERE bh.invoice_header_id = '" . $invoice_id . "' ORDER BY bh.invoice_header_id, bd.site_id";

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "mounting_print") {

    $mounting_id = intval($_REQUEST['mounting_id']);


    $query_header = "SELECT date_format(mh.action_date,'%d/%m/%Y') as invoice_date,mh.mounting_no as invoice_no,cm.pan,cm.gstin, date_format(now(),'%d/%m/%Y') as now, u.name, c.client_name, ifnull(c.address,'') AS client_address, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code, c.state_id,sm.state_code, c.pan vendor_pan, c.gstin vendor_gstin,cmt.contact_no_1,cmt.contact_no_2,cmt.email_id_1, cmt.email_id_2 
FROM mounting_header mh 
INNER JOIN user_master u ON u.user_id = mh.created_by 
INNER JOIN client_master c ON c.client_id = mh.vendor_id
LEFT JOIN contact_master cmt ON cmt.contact_ref_id=c.client_id
INNER JOIN company_master cm ON cm.company_id = mh.company_id
INNER JOIN state_master sm ON sm.state_id = c.state_id
WHERE mh.mounting_id ='" . $mounting_id . "'  AND contact_type = 'C' ";
    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT md.site_id, mh.mounting_id,lt.light_type_name, s.site_code, l.location_name, m.media_vh_name, md.total_size as sqft,md.total_size,md.mounting_rate,md.demounting_rate,md.repair_rate,md.mounting_flag,md.demounting_flag,md.repair_flag,md.mounting_amount,md.demounting_amount,md.repair_amount, s.width, s.height, s.sqft, s.face_side, st.site_type_name,(md.mounting_amount+md.demounting_amount+md.repair_amount) tot_amt,md.site_name,date_format(md.action_date,'%d/%m/%Y') action_date,md.mounting_typel,(md.mounting_rate + md.demounting_rate + md.repair_rate) sqft_rate  
FROM mounting_header mh
INNER JOIN mounting_detail md ON md.mounting_id = mh.mounting_id
INNER JOIN site_master s ON s.site_id = md.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
WHERE mh.mounting_id = '" . $mounting_id . "' 
ORDER BY md.action_date";
    //ORDER BY mh.mounting_id, md.site_id";
    //echo $query_detail;die;
    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    $tot_query = "select sum(md.mounting_amount+md.demounting_amount+md.repair_amount) tot_amt 
FROM mounting_header mh
INNER JOIN mounting_detail md ON md.mounting_id = mh.mounting_id
INNER JOIN site_master s ON s.site_id = md.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
WHERE mh.mounting_id = '" . $mounting_id . "'";

    $tot_header = $db->query($tot_query);
    $tot_amt = mysqli_fetch_assoc($tot_header);


    //echo $query_detail;die;

    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'tot_amt' => $tot_amt, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "printing_work_order") {

    $printing_id = intval($_REQUEST['printing_id']);
    //echo $printing_id;exit;


    $query_header = "SELECT date_format(ph.action_date,'%d/%m/%Y') as invoice_date,ph.printing_no as invoice_no,cm.pan,cm.gstin, date_format(now(),'%d/%m/%Y') as now, u.name, c.client_name, ifnull(c.address,'') AS client_address, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code, c.state_id, c.pan vendor_pan, c.gstin vendor_gstin
FROM printing_header ph 
INNER JOIN user_master u ON u.user_id = ph.created_by 
INNER JOIN client_master c ON c.client_id = ph.vendor_id 
INNER JOIN company_master cm ON cm.company_id = ph.company_id 
WHERE ph.printing_id ='" . $printing_id . "'";
    //echo $query_header;die;
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT pd.site_id, ph.printing_id,lt.light_type_name, s.site_code, l.location_name, m.media_vh_name, pd.total_size as sqft,pd.printing_rate,pd.printing_amount, s.width, s.height, s.sqft, s.face_side, st.site_type_name,pd.site_name,pd.display,date_format(pd.action_date,'%s/%m/%Y') action_date,pd.printing_rate 
FROM printing_header ph 
INNER JOIN printing_detail pd ON pd.printing_id = ph.printing_id 
INNER JOIN site_master s ON s.site_id = pd.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id 
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
WHERE ph.printing_id = '" . $printing_id . "' 
ORDER BY ph.printing_id, pd.site_id ";
    //echo $query_detail;die;
    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    $tot_query = "SELECT sum(pd.printing_amount) tot_amt 
FROM printing_header ph 
INNER JOIN printing_detail pd ON pd.printing_id = ph.printing_id 
INNER JOIN site_master s ON s.site_id = pd.site_id 
INNER JOIN location_master l ON l.location_id = s.location_id 
INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id
WHERE ph.printing_id = '" . $printing_id . "'";
    //echo $tot_query;die;
    $tot_header = $db->query($tot_query);
    $tot_amt = mysqli_fetch_assoc($tot_header);


    //echo $query_detail;die;

    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'tot_amt' => $tot_amt, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "manual_bill") {

    $invoice_id = intval($_REQUEST['invoice_id']);
    $query_header = "SELECT mih.invoice_header_id as invoice_id, mih.invoice_no,mih.after_discount, ifnull(date_format(mih.invoice_date,'%d/%m/%Y'),'') invoice_date,mih.po_no,ifnull(date_format(mih.po_date,'%d/%m/%Y'),'') po_date,mih.client_id,mih.client_name,ifnull(mih.client_address,'') client_address,ifnull(mih.client_address1,'') client_address1,ifnull(mih.client_address2,'') client_address2, mih.client_pan,mih.client_gstin,mih.client_name,IFNULL(st.state_code, '') client_state_code,mih.gross_total,mih.discount_percent,mih.discount_amount,mih.total_cgst,mih.total_sgst,mih.total_igst,mih.cgst_percentage,mih.sgst_percentage,mih.igst_percentage, mih.payable_amount, bam.account_no, bam.ifsc_no, bam.branch_name, bm.bank_name, bam.branch_address
	FROM manual_invoice_header mih  
	LEFT JOIN client_master c ON c.client_id = mih.client_id
	LEFT JOIN state_master st ON st.state_id = c.state_id
	LEFT JOIN bank_account_master bam ON bam.bank_account_id = mih.bank_account_id
	LEFT JOIN bank_master bm ON bm.bank_id = bam.bank_id
	WHERE mih.invoice_header_id = '" . $invoice_id . "'";

    //echo $query_header;die;   
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT invoice_header_id as invoice_id, description, hsn_sac_no, rate, size, ifnull(date_format(period_from,'%d/%m/%Y'),'') period_from,  ifnull(date_format(period_to,'%d/%m/%Y'),'') period_to, ifnull(rate,'') rate, amount,invoice_details_id 
    FROM manual_invoice_detail mid  
	WHERE invoice_header_id = '" . $invoice_id . "'";

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
} elseif ($action_type == "manual_estimate_bill") {

    $invoice_id = intval($_REQUEST['invoice_id']);
    $query_header = "SELECT mih.estimate_header_id as invoice_id, mih.invoice_no,mih.after_discount, ifnull(date_format(mih.invoice_date,'%d/%m/%Y'),'') invoice_date,mih.po_no,ifnull(date_format(mih.po_date,'%d/%m/%Y'),'') po_date,mih.client_id,mih.client_name,ifnull(mih.client_address,'') client_address,ifnull(mih.client_address1,'') client_address1,ifnull(mih.client_address2,'') client_address2, mih.client_pan,mih.client_gstin,mih.client_name,IFNULL(st.state_code, '') client_state_code,mih.gross_total,mih.discount_percent,mih.discount_amount,mih.total_cgst,mih.total_sgst,mih.total_igst,mih.cgst_percentage,mih.sgst_percentage,mih.igst_percentage, mih.payable_amount, bam.account_no, bam.ifsc_no, bam.branch_name, bm.bank_name, bam.branch_address
	FROM manual_estimate_header mih  
	LEFT JOIN client_master c ON c.client_id = mih.client_id
	LEFT JOIN state_master st ON st.state_id = c.state_id
	LEFT JOIN bank_account_master bam ON bam.bank_account_id = mih.bank_account_id
	LEFT JOIN bank_master bm ON bm.bank_id = bam.bank_id
	WHERE mih.estimate_header_id = '" . $invoice_id . "'";

    //echo $query_header;die;   
    $result_header = $db->query($query_header);
    $data_header = mysqli_fetch_assoc($result_header);

    $query_detail = "SELECT estimate_header_id as invoice_id, description, hsn_sac_no, rate, size, ifnull(date_format(period_from,'%d/%m/%Y'),'') period_from,  ifnull(date_format(period_to,'%d/%m/%Y'),'') period_to, ifnull(rate,'') rate, amount 
    FROM manual_estimate_detail mid  
	WHERE estimate_header_id = '" . $invoice_id . "'";

    $result_detail = $db->query($query_detail);
    while ($data_detail = mysqli_fetch_assoc($result_detail)) {
        $ret[] = $data_detail;
    }

    //echo $query_detail;die;


    $return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
    echo json_encode($return_data);
}




/**
 * Creates a templated slide
 * 
 * @param PHPPowerPoint $objPHPPowerPoint
 * @return PHPPowerPoint_Slide
 */
function createTemplatedSlide(PHPPowerPoint $objPHPPowerPoint)
{
    // Create slide
    $slide = $objPHPPowerPoint->createSlide();

    // Add background logo
    $shape = $slide->createDrawingShape();
    $shape->setName(APP_NAME . 'Logo');
    $shape->setDescription(APP_NAME . 'Background Logo');
    $shape->setPath('../assets/images/pptbg.jpg');
    $shape->setWidth(950);
    $shape->setHeight(720);
    $shape->setOffsetX(0);
    $shape->setOffsetY(0);
    // Return slide
    return $slide;
}

function createFrontPage(PHPPowerPoint $objPHPPowerPoint)
{
    // Create slide
    $slide = $objPHPPowerPoint->createSlide();

    // Add background logo
    $shape = $slide->createDrawingShape();
    $shape->setName(APP_NAME . 'Logo');
    $shape->setDescription(APP_NAME . 'Background Logo');
    $shape->setPath('../assets/images/frontpage.jpg');
    $shape->setWidth(950);
    $shape->setHeight(720);
    $shape->setOffsetX(0);
    $shape->setOffsetY(0);
    // Return slide
    return $slide;
}

function createBackPage(PHPPowerPoint $objPHPPowerPoint)
{
    // Create slide
    $slide = $objPHPPowerPoint->createSlide();

    // Add background logo
    $shape = $slide->createDrawingShape();
    $shape->setName(APP_NAME . 'Logo');
    $shape->setDescription(APP_NAME . 'Background Logo');
    $shape->setPath('../assets/images/backpage.jpg');
    $shape->setWidth(950);
    $shape->setHeight(720);
    $shape->setOffsetX(0);
    $shape->setOffsetY(0);
    // Return slide
    return $slide;
}
