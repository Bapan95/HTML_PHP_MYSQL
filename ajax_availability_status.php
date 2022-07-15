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
		$return_data  = array('status' => true, 'site_data' => $data, 'query' => $query);
	} else if ($return_type == "view_list") {
		$site_list = array();
		while ($data = mysqli_fetch_assoc($result)) {
			$site_list[] = $data;
		}
		$return_data  = array('status' => true, 'site_list' => $site_list);
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
		$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Media Vehicle');
		$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Width');
		$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Height');
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
} else if ($action_type == "DOWNLOAD_PPT") {
	//echo json_encode($_REQUEST); exit();
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

	$picture_type = "";
	if (!empty($_REQUEST['picture_type'])) {
		$picture_type = $_REQUEST['picture_type'];
	}


	$width = floatval($_REQUEST['width']);
	$height = floatval($_REQUEST['height']);
	$sqft = floatval($_REQUEST['sqft']);

	$query1 = "CALL get_available_sites_procedure(" . $logged_user_id . ", '" . $return_type . "', '" . $from_date . "', '" . $to_date . "', '" . $state . "', '" . $region . "', '" . $district . "', '" . $zone . "', '" . $location . "', '" . $site_type . "', '" . $media_vehicle . "', '" . $type . "', " . $width . ", " . $height . ", " . $sqft . ", '');";

	$result1 = $db->query($query1);
	if ($result1->num_rows > 0) {

		//	FOR PPT PREPARATION
		/** Include path **/
		set_include_path(get_include_path() . PATH_SEPARATOR . '../Classes/');

		/** PHPPowerPoint */
		require_once 'PHPPowerPoint.php';
		/** PHPPowerPoint_IOFactory */
		require_once 'PHPPowerPoint/IOFactory.php';
		// Create new PHPPowerPoint object
		$objPHPPowerPoint = new PHPPowerPoint();

		// Set properties
		$objPHPPowerPoint->getProperties()->setCreator("PHPPowerPoint");
		$objPHPPowerPoint->getProperties()->setLastModifiedBy(APP_NAME);
		$objPHPPowerPoint->getProperties()->setTitle(APP_NAME . "Site Pictures");
		$objPHPPowerPoint->getProperties()->setSubject(APP_NAME . "Site Description");
		$objPHPPowerPoint->getProperties()->setDescription(APP_NAME . "Site Description");
		$objPHPPowerPoint->getProperties()->setKeywords(APP_NAME . "Site Pictures");
		$objPHPPowerPoint->getProperties()->setCategory("Sample");

		// Remove first slide
		$objPHPPowerPoint->removeSlideByIndex(0);

		while ($row = mysqli_fetch_assoc($result1)) {
			// Create templated slide
			$currentSlide = createTemplatedSlide($objPHPPowerPoint); // local function

			$file_path = "img_not_available.jpg";
			if (($row[$picture_type] != "") && file_exists(APATH . "site-images/" . $row[$picture_type])) {
				$file_path = $row[$picture_type];
			}

			// Create a shape (drawing)
			$shape = $currentSlide->createDrawingShape();
			$shape->setName(APP_NAME . 'Site Picture');
			$shape->setDescription(APP_NAME . 'Site Picture');
			$shape->setPath('../site-images/' . $file_path);
			$shape->setHeight(544);
			$shape->setOffsetX(175);
			$shape->setOffsetY(135);
			//$shape->setRotation(25);
			$shape->getShadow()->setVisible(true);
			$shape->getShadow()->setDirection(45);
			$shape->getShadow()->setDistance(10);

			// Create a shape (text)
			$shape = $currentSlide->createRichTextShape();
			$shape->setHeight(120);
			$shape->setWidth(780);
			$shape->setOffsetX(160);
			$shape->setOffsetY(25);
			$shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
			$shape->getFill()->setFillType(PHPPowerPoint_Style_Fill::FILL_SOLID);

			// Create text run
			$textRun = $shape->createTextRun($row['site_name']);
			$textRun->getFont()->setBold(true);
			$textRun->getFont()->setSize(20);
			$textRun->getFont()->setColor(new PHPPowerPoint_Style_Color('FFFFFFFF'));
			$textRun->getFont()->setName('Century Gothic');

			$shape->createBreak();

			$textRun = $shape->createTextRun("Size: " . $row['width'] . "'x" . $row['height'] . "'" . " (" . $row['light_type_name'] . ")");
			$textRun->getFont()->setBold(true);
			$textRun->getFont()->setSize(20);
			$textRun->getFont()->setColor(new PHPPowerPoint_Style_Color('FFFFFFFF'));
			$textRun->getFont()->setName('Century Gothic');
		}
		$file_name =  APP_NAME . '_Availability_' . time();
		$ppt_file = $file_name . '.pptx';
		//	SAVE PPT FILE
		$xmlWriter = PHPPowerPoint_IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
		//$xmlWriter->save(APATH . "available-sites/" . "{$ppt_file}");
		/*ob_start();
		header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
		header('Content-Disposition: attachment;filename="'."{$file_name}.pptx".'"');
		header('Cache-Control: max-age=0');
		$xmlWriter->save("php://output");*/

		$xmlWriter->save("/tmp/{$ppt_file}");
		//$xmlWriter->save(APATH . "available-sites/{$ppt_file}");

		//$pptData = ob_get_contents();
		//ob_end_clean();
		//$file_path = URL ."available-sites/" . "{$ppt_file}";
		//$return_data =  array('status' => true,'file_name'=>$file_name,'file_path'=>$file_path);
		$return_data =  array(
			'status' => true, 'file_name' => $ppt_file, 'qry' => $query1/*,
			'file' => "data:application/vnd.openxmlformats-officedocument.presentationml.presentation;base64,".base64_encode($pptData)*/
		);
	}
	echo json_encode($return_data);
	exit;
} elseif ($action_type == "SEND_EMAIL") {
	$selected_client = $_REQUEST['client_id'];
	$select_client = implode(",", $selected_client);
	$client_type = $_REQUEST['client_type'];
	$body = $_REQUEST['mail_body'];
	$subject = $_REQUEST['subject'];
	$email_addr = '';
	if ($client_type == 'R') {
		$query = "SELECT contact_person_name,contact_no_1,ifnull(email_id_1,'') email_id_1,ifnull(email_id_2,'') email_id_2 from contact_master 
		WHERE email_id_1<>'' AND contact_type='C' AND  contact_ref_id IN (" . $select_client . ")";
		$result = $db->query($query);

		while ($data = mysqli_fetch_assoc($result)) {
			$email_address .= $data['email_id_1'] . ',';
			if (!empty($data['email_id_2'])) {
				$email_address .= $data['email_id_2'] . ',';
			}
		}
		$email_addr = rtrim($email_address, ",");
	} else {
		$email_addr = $_REQUEST['email_address'];
	}

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

	$pic_type = "";
	if (!empty($_REQUEST['pic_type'])) {
		$pic_type = $_REQUEST['pic_type'];
	}


	$width = floatval($_REQUEST['width']);
	$height = floatval($_REQUEST['height']);
	$sqft = floatval($_REQUEST['sqft']);

	$picture_type = "";
	if (!empty($_REQUEST['pic_type'])) {
		$picture_type = $_REQUEST['pic_type'];
	}

	$query1 = "CALL get_available_sites_procedure(" . $logged_user_id . ", '" . $return_type . "', '" . $from_date . "', '" . $to_date . "', '" . $state . "', '" . $region . "', '" . $district . "', '" . $zone . "', '" . $location . "', '" . $site_type . "', '" . $media_vehicle . "', '" . $type . "', " . $width . ", " . $height . ", " . $sqft . ", '');";

	$result1 = $db->query($query1);
	if ($result1->num_rows > 0) {
		//	FOR EXCEL PREPARATION
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
		$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Media Vehicle');
		$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Width');
		$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Height');
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
				'color' => array('rgb' => '000000'),
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


		//	FOR PPT PHPPowerPoint
		/** Include path **/
		set_include_path(get_include_path() . PATH_SEPARATOR . '../Classes/');

		/** PHPPowerPoint */
		require_once 'PHPPowerPoint.php';
		/** PHPPowerPoint_IOFactory */
		require_once 'PHPPowerPoint/IOFactory.php';
		// Create new PHPPowerPoint object
		$objPHPPowerPoint = new PHPPowerPoint();

		// Set properties
		$objPHPPowerPoint->getProperties()->setCreator("PHPPowerPoint");
		$objPHPPowerPoint->getProperties()->setLastModifiedBy(APP_NAME);
		$objPHPPowerPoint->getProperties()->setTitle(APP_NAME . "Site Pictures");
		$objPHPPowerPoint->getProperties()->setSubject(APP_NAME . "Site Description");
		$objPHPPowerPoint->getProperties()->setDescription(APP_NAME . "Site Description");
		$objPHPPowerPoint->getProperties()->setKeywords(APP_NAME . "Site Pictures");
		$objPHPPowerPoint->getProperties()->setCategory("Sample");

		// Remove first slide
		$objPHPPowerPoint->removeSlideByIndex(0);

		while ($row = mysqli_fetch_assoc($result1)) {
			//print_r($row);exit;  
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


			// Create templated slide
			$currentSlide = createTemplatedSlide($objPHPPowerPoint); // local function

			$file_path = "img_not_available.jpg";
			if (($row[$picture_type] != "") && file_exists(APATH . "site-images/" . $row[$picture_type])) {
				$file_path = $row[$picture_type];
			}

			// Create a shape (drawing)
			$shape = $currentSlide->createDrawingShape();
			$shape->setName(APP_NAME . 'Site Picture');
			$shape->setDescription(APP_NAME . 'Site Picture');
			$shape->setPath('../site-images/' . $file_path);
			$shape->setHeight(544);
			$shape->setOffsetX(175);
			$shape->setOffsetY(135);
			//$shape->setRotation(25);
			$shape->getShadow()->setVisible(true);
			$shape->getShadow()->setDirection(45);
			$shape->getShadow()->setDistance(10);

			// Create a shape (text)
			$shape = $currentSlide->createRichTextShape();
			$shape->setHeight(120);
			$shape->setWidth(780);
			$shape->setOffsetX(160);
			$shape->setOffsetY(25);
			$shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
			$shape->getFill()->setFillType(PHPPowerPoint_Style_Fill::FILL_SOLID);

			// Create text run
			$textRun = $shape->createTextRun($row['site_name']);
			$textRun->getFont()->setBold(true);
			$textRun->getFont()->setSize(20);
			$textRun->getFont()->setColor(new PHPPowerPoint_Style_Color('FFFFFFFF'));
			$textRun->getFont()->setName('Century Gothic');

			$shape->createBreak();

			$textRun = $shape->createTextRun("Size: " . $row['width'] . "'x" . $row['height'] . "'" . " (" . $row['light_type_name'] . ")");
			$textRun->getFont()->setBold(true);
			$textRun->getFont()->setSize(20);
			$textRun->getFont()->setColor(new PHPPowerPoint_Style_Color('FFFFFFFF'));
			$textRun->getFont()->setName('Century Gothic');
		}
		//	SAVE EXCEL
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		/*ob_start();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'."disbursed_report".date('jS-F-y H-i-s').".xlsx".'"');
		header('Cache-Control: max-age=0');*/

		$file_name =  APP_NAME . '_Availability_' . time();
		$excel_file = $file_name . '.xlsx';
		$ppt_file = $file_name . '.pptx';
		$objWriter->save(APATH . "available-sites/{$excel_file}");
		$xlsData = ob_get_contents();
		ob_end_clean();

		//	SAVE PPT FILE
		$xmlWriter = PHPPowerPoint_IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
		$xmlWriter->save(APATH . "available-sites/" . "{$ppt_file}");

		$db->next_result();
		$query_user_mail = "Select email from user_master Where user_id=" . $logged_user_id;
		$result_user_mail = $db->query($query_user_mail);
		$row_mail = mysqli_fetch_assoc($result_user_mail);


		$to = $email_addr;
		//$body = '<!DOCTYPE html><html><body>' . $body . '</body></html>';

		$body = '<!DOCTYPE html><html><body>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					  <tr>
						<td colspan="2" style="padding-bottom:10px;"><p>' . $body . '</p></td>
					  </tr>
					  <tr>
						<td colspan="2" style="padding-bottom:25px;"><h3>Click on the below icons to Download Files</h3></td>
					  </tr>
					  <tr>
						<td width="90"><a href="' . URL . 'available-sites/' . $excel_file . '" target="_blank"><img src="' . URL . 'assets/images/xls_download.png" width="60" height="60" alt=""/></a></td>
						<td><a href="' . URL . 'available-sites/' . $ppt_file . '" target="_blank"><img src="' . URL . 'assets/images/ppt_download.png" width="60" height="60" alt=""/></a></td>
					  </tr>
					</table>
				</body></html>';
		/*
		$excel_link = '<a href="' . URL . 'available-sites/' . $excel_file . '" target="_blank"><img src="' . URL . 'assets/images/xls_download.jpg" /></a>';
		$ppt_link='<a href="' . URL . 'available-sites/' . $ppt_file . '" target="_blank"><img src="' . URL . 'assets/images/ppt_download.jpg" /></a>';
		$body = str_replace("[~~DOWNLOAD_EXCEL_LINK~~]", $excel_link, $body);
		$body = str_replace("[~~DOWNLOAD_PPT_LINK~~]", $ppt_link, $body);*/
		//$body=$excel_link;

		$headers = 'From: ' . ADMIN_MAIL . "\r\n";
		$headers .= 'CC: ' . $row_mail['email'] . "\r\n";
		$headers .= 'Reply-To: ' . $to . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "X-Priority: 1 (Highest)\r\n";
		$headers .= "X-MSMail-Priority: High\r\n";
		$headers .= "Importance: High\r\n";
		$headers .= "X-Mailer: PHP" . phpversion() . "\r\n";




		/*$headers = 'From: ' . ADMIN_MAIL . "\r\n" ;
		$headers .='CC: ' . $row_mail['email'] . "\r\n";
		$headers .='Reply-To: '. $to . "\r\n" ;
		$headers .='X-Mailer: PHP/' . phpversion();
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";*/

		if (mail($to, $subject, $body, $headers)) {
			$return_data  = array('status' => true, 'excel_link' => $excel_link, 'email_address' => $email_addr, 'qry' => $row_mail, 'return_data' => $body, 'headers' => $headers);
		} else {
			$return_data  = array('status' => false, 'excel_link' => $excel_link, 'email_address' => $email_addr, 'qry' => $row_mail, 'return_data' => $body, 'msg' => "Unable to send email.");
		}
	}
	echo json_encode($return_data);
} elseif ($action_type == "SUBMIT_SITE") {
	$selected_site = $_REQUEST['selected_site'];
	$from_date = implode('-', array_reverse(explode('/', $_REQUEST['from_date'])));
	$to_date = implode('-', array_reverse(explode('/', $_REQUEST['to_date'])));
	//pre($selected_site);
	//$selected_site=ltrim($selected_site,",");
	$query = "CALL site_wise_available_portion(" . $logged_user_id . ", '" . implode(',', $selected_site) . "', '" . $from_date . "', '" . $to_date . "');";
	/*$query = "SELECT s.site_id,s.site_name,s.site_code,l.location_name,st.site_type_name,m.media_vh_name,s.face_side,s.width,s.height,s.sqft,s.display_charges FROM site_master s 
	INNER JOIN location_master l ON l.location_id=s.location_id
	INNER JOIN site_type_master  st ON st.site_type_id=s.site_type_id
	INNER JOIN media_vehicle m ON m.media_vh_id=s.media_vh_id
	WHERE s.site_id IN (".implode(',', $selected_site).")";*/
	$result = $db->query($query);

	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$db->next_result();

	$query1 = "SELECT lookup_id,lookup_desc from lookup_table WHERE lookup_type='tenure'";
	$result1 = $db->query($query1);

	while ($data1 = mysqli_fetch_assoc($result1)) {
		$ret1[] = $data1;
	}
	$query2 = "SELECT lookup_id,lookup_desc from lookup_table WHERE lookup_type='booking_portion'";
	$result2 = $db->query($query2);

	while ($data2 = mysqli_fetch_assoc($result2)) {
		$ret2[] = $data2;
	}
	$return_data  = array('status' => true, 'selected_site_list' => $ret, 'tenure_list' => $ret1, 'booking_portion_list' => $ret2);

	echo json_encode($return_data);
} elseif ($action_type == "CLIENT") {
	$query = "SELECT client_id,client_name,client_code from client_master";
	// echo $query;die;	
	$result = $db->query($query);

	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'client_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "COMPANY") {
	$query = "SELECT company_id,company_name from company_master";
	$result = $db->query($query);

	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'company_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_BOOKING") {
	//pre($_REQUEST);
	$form_data = '';
	/*$string_selected_site=$_REQUEST['string_selected_site'];
 $string_selected_site=ltrim($string_selected_site,",");
 $string_selected_site=(explode(",",$string_selected_site));*/
	$selected_site_arr = explode(',', $_REQUEST['selected_site_arr']);
	foreach ($selected_site_arr as $selected_site) {
		$form_data .= $selected_site . ',' . $_REQUEST['upper_left' . $selected_site] . ',' . $_REQUEST['upper_right' . $selected_site] . ',' . $_REQUEST['lower_left' . $selected_site] . ',' . $_REQUEST['lower_right' . $selected_site] . ',' . $_REQUEST['extension_prospect' . $selected_site] . ',' . $_REQUEST['display_charges' . $selected_site] . ',' . $_REQUEST['tenure' . $selected_site] . '|';
	}
	$form_data = rtrim($form_data, "|");
	$start_date = implode('-', array_reverse(explode('/', $_REQUEST['start_date_hidden'])));
	$end_date = implode('-', array_reverse(explode('/', $_REQUEST['end_date_hidden'])));
	$billing_from = implode('-', array_reverse(explode('/', $_REQUEST['billing_from'])));
	$billing_to = implode('-', array_reverse(explode('/', $_REQUEST['billing_to'])));
	$contract_date = implode('-', array_reverse(explode('/', $_REQUEST['contract_date'])));
	$mail_confirmation_date = implode('-', array_reverse(explode('/', $_REQUEST['mail_confirmation_date'])));

	$parent_booking_id = intval($_REQUEST['parent_booking_id']);

	$mail_file_name = '';
	$mail_file = $_FILES['mail_file']['name'];
	if (!empty($mail_file)) {
		$file_ext = strtolower(end(explode(".", $mail_file)));
		//$allowed_ext = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx");
		$allowed_ext = array("jpg", "jpeg", "png", "pdf");
		if (in_array($file_ext, $allowed_ext)) {
			$file_name = 'mail_file_' . time() . '.' . $file_ext;
			$sourcePath = $_FILES['mail_file']['tmp_name'];
			$targetPath = "../upload_file/" . $file_name;
			if (move_uploaded_file($sourcePath, $targetPath)) {
				$mail_file_name = $file_name;
			}
		}
	}

	$query = "CALL add_edit_booking_proc('" . $_REQUEST['company'] . "', " . $_REQUEST['client'] . ", '" . $start_date . "', '" . $end_date . "', '" . $_REQUEST['brand_name'] . "', '" . $_REQUEST['remarks'] . "', " . $_REQUEST['rent_applicable'] . ", b'" . $_REQUEST['automail_exclude_flag'] . "', b'" . $_REQUEST['mail_confirmation_flag'] . "','" . $mail_confirmation_date . "', b'" . $_REQUEST['po_generated'] . "', '" . $billing_from . "', '" . $billing_to . "', " . $_REQUEST['booked_by'] . ", " . $logged_user_id . ", '" . $form_data . "', " . $parent_booking_id . ", '" . $_REQUEST['package_amount'] . "', '" . $_REQUEST['po_number'] . "','" . $_REQUEST['contract_sign'] . "','" . $contract_date . "','" . $_REQUEST['gst_applicable'] . "', '" . $mail_file_name . "');";
	$result = $db->query($query);
	$booking_result = mysqli_fetch_assoc($result);
	$booking_no = $booking_result['booking_no'];
	$return_data  = array('status' => true, 'query' => $query, 'booking_result' => $booking_result, 'booking_no' => $booking_no);
	echo json_encode($return_data);
} elseif ($action_type == "BOOKING_LISTING") {

	$query = "CALL booking_list_acc_user_proc (" . $logged_user_id . ");";

	$result = $db->query($query);

	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'booking_list' => $ret, 'query' => $query);
	echo json_encode($return_data);
} elseif ($action_type == "BOOKING_DETAIL") {
	$booking_id = intval($_REQUEST['booking_id']);
	$query = "SELECT bh.booking_id, ifnull(bh.parent_booking_id, bh.booking_id) parent_booking_id, bh.booking_no, bh.remarks, date_format(bh.booking_from, '%d-%m-%Y') booking_from, date_format(bh.booking_to,'%d-%m-%Y') booking_to,bh.billing_to billing_to_date, date_format(bh.billing_from,'%d-%m-%Y') billing_from, date_format(bh.billing_to, '%d-%m-%Y') billing_to, bh.rent_applicable_flag, bh.mail_confirmation_flag, date_format(bh.mail_confirmation_date,'%d/%m/%Y') mail_confirmation_date, bh.brand_name, bh.company_id,bh.client_id,bh.invoice_generated_flag, bh.po_generated_flag,ifnull(bh.package_amount,'N/A') package_amount,ifnull(bh.po_number,'N/A') po_number,bh.contract_flag,date_format(bh.contract_date,'%d/%m/%Y') contract_date,bh.gst_applicable_flag,c.client_name, cm.company_name, IFNULL(bh.booked_by, 0) booked_by, IFNULL(u.name, 'NA') AS booked_user_name, IFNULL(bh.mail_file, '') mail_file 
	FROM booking_header bh
	LEFT JOIN user_master u ON u.user_id = bh.booked_by
	INNER JOIN client_master c ON c.client_id=bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE bh.booking_id = " . $booking_id;
	$result = $db->query($query);
	$booking_info = mysqli_fetch_assoc($result);
	if ($booking_info['mail_file'] != '') {
		if (!file_exists(APATH . 'upload_file/' . $booking_info['mail_file'])) {
			$booking_info['mail_file'] = '';
		}
	}

	$query_detail = "SELECT bd.site_id, s.site_name, s.site_code, l.location_name, m.media_vh_name, get_booked_portion(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr) AS booked_portion, bd.extension_prospect, bd.booking_amount, s.width, s.height, s.sqft, s.face_side, t.lookup_desc, st.site_type_name,bd.booking_detail_id FROM booking_detail bd
	INNER JOIN site_master s ON s.site_id=bd.site_id
	INNER JOIN location_master l ON l.location_id=s.location_id
	INNER JOIN media_vehicle m ON m.media_vh_id=s.media_vh_id
	INNER JOIN site_type_master st ON st.site_type_id=s.site_type_id
	LEFT JOIN lookup_table t ON t.lookup_id=bd.tenure_id
	WHERE bd.booking_id=" . $booking_id;

	$result_detail = $db->query($query_detail);
	while ($data = mysqli_fetch_assoc($result_detail)) {
		$ret[] = $data;
	}
	$query_invoice_cnt = "SELECT count(invoice_id) AS cnt FROM invoice_master WHERE booking_id=" . $booking_id;
	$result_invoice_cnt = $db->query($query_invoice_cnt);
	$invoice_cnt = mysqli_fetch_assoc($result_invoice_cnt);
	$invoice_cnt_1 = $invoice_cnt['cnt'];
	$invoice_details = "";
	if ($invoice_cnt_1 != 0) {
		$query_invoice_details = "SELECT invoice_id, invoice_no, eff_end_date FROM invoice_master WHERE eff_end_date=(SELECT MAX(eff_end_date) FROM invoice_master WHERE booking_id='" . $booking_id . "');";
		$result_invoice_details = $db->query($query_invoice_details);
		$invoice_details = mysqli_fetch_assoc($result_invoice_details);
	}
	$today = date('Y-m-d');
	if ($invoice_cnt_1 == 0) {
		$msg = "No Invoice Genarated";
	} elseif ((strtotime($invoice_details['eff_end_date']) <= strtotime($today) || strtotime($invoice_details['eff_end_date']) <= strtotime($booking_info['billing_to_date']))) {
		$invoice_date = $invoice_details['eff_end_date'];
		$msg = $invoice_details['invoice_no'];
	} elseif (strtotime($invoice_details['eff_end_date']) < strtotime($booking_info['billing_to_date'])) {
		$msg = "Invoice status is over";
	} else {
		$msg = "Invoice status is over";
	}
	$return_data  = array('status' => true, 'qry' => $query, 'booking_info' => $booking_info, 'booking_list' => $ret, 'invoice_cnt' => $invoice_cnt_1, 'invoice_details' => $invoice_details, 'msg' => $msg);
	echo json_encode($return_data);
} elseif ($action_type == "EDIT_BOOKING") {
	$booking_id = intval($_REQUEST['booking_id']);
	$po_number = $_REQUEST['po_number'];
	$package_amount = $_REQUEST['package_amount'];
	$billing_from = implode('-', array_reverse(explode('/', $_REQUEST['billing_from'])));
	$billing_to = implode('-', array_reverse(explode('/', $_REQUEST['billing_to'])));
	$contract_date = implode('-', array_reverse(explode('/', $_REQUEST['contract_date'])));
	$client = $_REQUEST['client'];
	$company = $_REQUEST['company'];
	$booked_by = $_REQUEST['booked_by'];
	$rent_applicable = $_REQUEST['rent_applicable'];
	$brand_name = $_REQUEST['brand_name'];
	$po_generated = $_REQUEST['po_generated'];
	$contract_sign = $_REQUEST['contract_sign'];
	$gst_applicable = $_REQUEST['gst_applicable'];
	$mail_confirmation_flag = $_REQUEST['mail_confirmation_flag'];
	$mail_confirmation_date = implode('-', array_reverse(explode('/', $_REQUEST['mail_confirmation_date'])));
	$remarks = $_REQUEST['remarks'];

	$mail_file_name = '';
	$mail_file = $_FILES['mail_file']['name'];
	if (!empty($mail_file)) {
		$file_ext = strtolower(end(explode(".", $mail_file)));
		//$allowed_ext = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx");
		$allowed_ext = array("jpg", "jpeg", "png", "pdf");
		if (in_array($file_ext, $allowed_ext)) {
			$file_name = 'mail_file_' . time() . '.' . $file_ext;
			$sourcePath = $_FILES['mail_file']['tmp_name'];
			$targetPath = "../upload_file/" . $file_name;
			if (move_uploaded_file($sourcePath, $targetPath)) {
				$mail_file_name = $file_name;
			}
		}
	}

	$form_data = '';
	$i = 0;
	foreach ($_REQUEST['booking_detail_id'] as $booking_detail_id) {
		$form_data .= $booking_detail_id . ',' . $_REQUEST['booking_amount'][$i] . '|';
		$i++;
	}
	$form_data = rtrim($form_data, "|");

	$query = "CALL edit_booking_proc({$booking_id}, '{$billing_from}', '{$billing_to}', '{$client}', '{$company}', '{$booked_by}', b'{$rent_applicable}', b'{$mail_confirmation_flag}', '{$mail_confirmation_date}', '{$brand_name}', b'{$po_generated}', '{$po_number}', '{$package_amount}','{$remarks}', '{$mail_file_name}', '" . $form_data . "', " . $logged_user_id . ",'" . $contract_date . "','" . $contract_sign . "','" . $gst_applicable . "');";

	$result = $db->query($query);
	$booking_result = mysqli_fetch_assoc($result);
	$update_status = $booking_result['msg'];
	$return_data = array('status' => true, 'query' => $query, 'booking_result' => $booking_result, 'update_status' => $update_status);

	echo json_encode($return_data);
} elseif ($action_type == "BOOKED_DETAIL") {
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$client = $_REQUEST['client'];
	$user = $_REQUEST['user'];

	$query = "CALL booked_list_acc_user_proc('" . $from_date . "','" . $to_date . "','" . $user . "','" . $client . "','" . $logged_user_id . "')";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'qry' => $query, 'booking_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "INVOICE_GENERATE") {

	$booking_id = intval($_REQUEST['booking_id']);

	$query_header = "SELECT bh.booking_no,bh.remarks,get_booking_from_func(bh.booking_id) booking_from, get_booking_to_func(bh.booking_id) booking_to, date_format(bh.billing_from,'%d/%m/%Y') billing_from, date_format(bh.billing_to,'%d/%m/%Y') billing_to, is_extended_func(bh.booking_id) is_extended, bh.rent_applicable_flag, bh.po_generated_flag, bh.mail_confirmation_flag, bh.created_by, date_format(bh.created_ts,'%d/%m/%Y') book_date, u.name, c.client_name, ifnull(c.address,'') AS client_address, bh.brand_name, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code FROM booking_header bh
	INNER JOIN user_master u ON u.user_id = bh.created_by
	INNER JOIN client_master c ON c.client_id = bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE bh.booking_id = " . $booking_id;
	$result_header = $db->query($query_header);
	$data_header = mysqli_fetch_assoc($result_header);

	$query_detail = "SELECT  bh.booking_id, bd.site_id, bh.booking_no,bh.rent_applicable_flag, date_format(bh.booking_from, '%d/%m/%Y') booking_from, date_format(bh.booking_to, '%d/%m/%Y') booking_to, month_day_diff(bh.billing_from,bh.billing_to)  day_num,bh.package_amount,lt.light_type_name, s.site_name, s.site_code, l.location_name, m.media_vh_name,get_booked_portion(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr) AS booked_portion, bd.extension_prospect, bd.booking_amount, s.width, s.height, s.sqft, s.face_side, t.lookup_desc, st.site_type_name FROM booking_header bh  
	INNER JOIN booking_detail bd ON bd.booking_id = bh.booking_id
	INNER JOIN site_master s ON s.site_id = bd.site_id
	INNER JOIN location_master l ON l.location_id = s.location_id
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id
	LEFT JOIN lookup_table t ON t.lookup_id = bd.tenure_id
	WHERE bh.cancel_status = 0 AND (bh.booking_id = " . $booking_id . " OR bh.parent_booking_id = " . $booking_id . ") ORDER BY bh.booking_id, bd.site_id";
	$result_detail = $db->query($query_detail);
	while ($data_detail = mysqli_fetch_assoc($result_detail)) {
		$ret[] = $data_detail;
	}


	$return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'query_detail' => $query_detail, 'booking_info' => $data_header, 'booking_detail' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "BOOKING_INFO") {
	$booking_id = $_REQUEST['booking_id'];
	$query_header = "SELECT bh.booking_no, bh.company_id, bh.client_id, bh.remarks, date_format(bh.booking_from,'%d/%m/%Y') booking_from, date_format(bh.booking_to, '%d/%m/%Y') booking_to, date_format(bh.billing_from,'%d/%m/%Y') billing_from, date_format(bh.billing_to, '%d/%m/%Y') billing_to, bh.rent_applicable_flag, bh.po_generated_flag, bh.invoice_generated_flag, bh.created_by, date_format(bh.created_ts, '%d/%m/%Y') book_date, u.name, c.client_name, ifnull(c.address, '') AS client_address, bh.brand_name, cm.company_name, cm.company_shortname, cm.address, cm.village_town, cm.pin_code FROM booking_header bh
	INNER JOIN user_master u ON u.user_id=bh.created_by
	INNER JOIN client_master c ON c.client_id=bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE bh.booking_id = " . $booking_id;

	$result_header = $db->query($query_header);
	$data_header = mysqli_fetch_assoc($result_header);

	$return_data = array('status' => true, 'qry' => $query, 'booking_info' => $data_header);
	echo json_encode($return_data);
} elseif ($action_type == "OOH_DETAIL") {

	$booking_id = intval($_REQUEST['booking_id']);

	$query_header = "SELECT ifnull(bh.po_number,'N/A') po_number,ifnull(bh.package_amount,'N/A') package_amount,bh.mail_confirmation_flag,date_format(bh.mail_confirmation_date,'%d/%m/%Y') mail_confirmation_date,bh.booking_no,bh.remarks,get_booking_from_func(bh.booking_id) booking_from, get_booking_to_func(bh.booking_id) booking_to, get_billing_from_func(bh.booking_id) billing_from, get_billing_to_func(bh.booking_id) billing_to,bh.created_by,IFNULL(u.name, 'NA') AS created_by_user, date_format(bh.created_ts,'%d/%m/%Y') book_date, u.name, c.client_name, cm.company_name FROM booking_header bh
	INNER JOIN user_master u ON u.user_id = bh.created_by
	INNER JOIN client_master c ON c.client_id = bh.client_id
	INNER JOIN company_master cm ON cm.company_id = bh.company_id
	WHERE bh.booking_id = " . $booking_id;
	$result_header = $db->query($query_header);
	$data_header = mysqli_fetch_assoc($result_header);
	// echo $query_header;
	// die();

	$query_detail = "SELECT distinct (bd.site_id), s.site_name, s.site_code, l.location_name, m.media_vh_name,s.width, s.height, s.sqft,get_booked_portion(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr) as booked_portion, 
	get_booked_sqft(bd.portion_ul, bd.portion_ur, bd.portion_ll, bd.portion_lr, s.sqft) as booked_sqft, bd.booking_amount, s.face_side, st.site_type_name , s.site_qty,get_billing_from_func(bh.booking_id) billing_from, get_billing_to_func(bh.booking_id) billing_to
	FROM booking_header bh 
	INNER JOIN booking_detail bd ON bd.booking_id = bh.booking_id 
	INNER JOIN site_master s ON s.site_id = bd.site_id 
	INNER JOIN location_master l ON l.location_id = s.location_id 
	INNER JOIN media_vehicle m ON m.media_vh_id = s.media_vh_id 
	INNER JOIN site_type_master st ON st.site_type_id = s.site_type_id 
	INNER JOIN light_type_master lt ON lt.light_type_id = s.light_type_id 
	LEFT JOIN lookup_table t ON t.lookup_id = bd.tenure_id 
	WHERE bh.cancel_status = 0 AND (bh.booking_id = " . $booking_id . " OR bh.parent_booking_id = " . $booking_id . ") 
	ORDER BY bh.booking_id, bd.site_id;";
	//echo $query_detail;die;
	$result_detail = $db->query($query_detail);
	while ($data_detail = mysqli_fetch_assoc($result_detail)) {
		$ret[] = $data_detail;
	}

	$return_data = array('status' => true, 'qry' => $db->last_query(), 'query_header' => $query_header, 'booking_list' => $ret, 'booking_info' => $data_header, 'booking_detail' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "DOWNLOAD_EXCEL") {
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$from_date = date('Y-m-d', strtotime($from_date));
	$to_date = date('Y-m-d', strtotime($to_date));
	$client = $_REQUEST['client'];
	$user = $_REQUEST['user'];
	$query = "CALL booked_list_acc_user_proc('" . $from_date . "','" . $to_date . "','" . $user . "','" . $client . "','" . $logged_user_id . "')";
	$result = $db->query($query);



	$objPHPExcel = new PHPExcel();
	// Set the active Excel worksheet to sheet 0 
	$objPHPExcel->setActiveSheetIndex(0);
	// Initialise the Excel row number 

	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'SL No.');
	$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Client Name');
	$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Site Code');
	$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Site/Hording Name');
	$objPHPExcel->getActiveSheet()->setCellValue('E1', 'State');
	$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Region');
	$objPHPExcel->getActiveSheet()->setCellValue('G1', 'District');
	$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Zone');
	$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Location');
	$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Site Type');
	$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Media Vehicle');
	$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Unit');
	$objPHPExcel->getActiveSheet()->setCellValue('M1', 'Face');
	$objPHPExcel->getActiveSheet()->setCellValue('N1', 'Height');
	$objPHPExcel->getActiveSheet()->setCellValue('O1', 'Width');
	$objPHPExcel->getActiveSheet()->setCellValue('P1', 'Total Size(Sqft)');
	$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Type');
	$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Booking From');
	$objPHPExcel->getActiveSheet()->setCellValue('S1', 'Booking To');
	$objPHPExcel->getActiveSheet()->setCellValue('T1', 'Booking Amount');
	$objPHPExcel->getActiveSheet()->setCellValue('U1', 'Booked Portion');
	$objPHPExcel->getActiveSheet()->setCellValue('V1', 'Billing From');
	$objPHPExcel->getActiveSheet()->setCellValue('W1', 'Billing To');
	$objPHPExcel->getActiveSheet()->setCellValue('X1', 'Extension Prospect');
	$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'Booked By');

	$styleArray = array(
		'font'  => array(
			'bold'  => true,
			'color' => array('rgb' => '000000'),


		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:Y1')->applyFromArray($styleArray);

	foreach (range('A', 'Y') as $columnID) {
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

		if ($row['extension_prospect'] == 1) {
			$extension_prospect = 'Yes';
		} else {
			$extension_prospect = '';
		}
		$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


		$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $row['client_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $row['site_code']);
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $row['site_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $row['state_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $row['region_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCount, $row['district_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('H' . $rowCount, $row['zone_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('I' . $rowCount, $row['location_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $rowCount, $row['site_type_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $rowCount, $row['media_vh_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('L' . $rowCount, $row['site_qty']);
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $rowCount, $row['face_side']);
		$objPHPExcel->getActiveSheet()->setCellValue('N' . $rowCount, $row['height']);
		$objPHPExcel->getActiveSheet()->setCellValue('O' . $rowCount, $row['width']);
		$objPHPExcel->getActiveSheet()->setCellValue('P' . $rowCount, $row['sqft']);
		$objPHPExcel->getActiveSheet()->setCellValue('Q' . $rowCount, $row['light_type_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('R' . $rowCount, $row['booking_from']);
		$objPHPExcel->getActiveSheet()->setCellValue('S' . $rowCount, $row['booking_to']);
		$objPHPExcel->getActiveSheet()->setCellValue('T' . $rowCount, $row['booking_amount']);
		$objPHPExcel->getActiveSheet()->setCellValue('U' . $rowCount, $row['booked_portion']);
		$objPHPExcel->getActiveSheet()->setCellValue('V' . $rowCount, $row['billing_from']);
		$objPHPExcel->getActiveSheet()->setCellValue('W' . $rowCount, $row['billing_to']);
		$objPHPExcel->getActiveSheet()->setCellValue('X' . $rowCount, $extension_prospect);
		$objPHPExcel->getActiveSheet()->setCellValue('Y' . $rowCount, $row['user_name']);
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

	$file_name = 'List_Of_Booked_Sites' . $today;
	$return_data =  array(
		'status' => true, 'file_name' => $file_name,
		'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
	);
	echo json_encode($return_data);
	exit;
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
   
/*
function createTemplatedSlide(PhpOffice\PhpPresentation\PhpPresentation $objPHPPresentation)
{
    // Create slide
    $slide = $objPHPPresentation->createSlide();
    
    // Add logo
    $shape = $slide->createDrawingShape();
    $shape->setName('PHPPresentation logo')
        ->setDescription('PHPPresentation logo')
        ->setPath('../assets/images/pptbg.jpg')
        ->setHeight(719)
        ->setOffsetX(0)
        ->setOffsetY(0);
    $shape->getShadow()->setVisible(true)
        ->setDirection(45)
        ->setDistance(10);

    // Return slide
    return $slide;
}*/
