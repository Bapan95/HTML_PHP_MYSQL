<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
$logged_user_id = my_session('user_id');
$role_id = my_session('role_id');
if (isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')) {
	$logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$return_data  = array();

if ($action_type == "PROJECT_LIST") {
	$from_date = date('Y-m_d', strtotime($_REQUEST['from_date']));
	$to_date = date('Y-m-d', strtotime($_REQUEST['to_date']));
	$client = $_REQUEST['client'];
	$status = $_REQUEST['status'];
	$ref_type = $_REQUEST['ref_type'];

	/*$query = "SELECT p.project_id,p.provisional_no,p.project_status_id,ifnull(p.project_no,'') project_no,p.is_active,ifnull(s.status_name,'') project_status,p.project_cost,date_format(p.created_ts,'%d-%m-%Y') created_ts,ifnull(p.po_cutoff_date,'0') po_cutoff_date,c.client_name, case when p.project_status_id>=13 then DATEDIFF(p.completed_date,p.created_ts) 
end day_diff,ifnull(date_format(p.completed_date,'%d-%m-%Y'),'0000-00-00') completed_date FROM project_header p LEFT JOIN client_master c ON p.client_id=c.client_id
    INNER JOIN project_status s ON p.project_status_id=s.project_status_id 
     WHERE (p.created_ts BETWEEN '".$from_date."' AND date_add('".$to_date."', INTERVAL 1 DAY))";
     if(!empty($client))
     {
     	$query .= "AND p.client_id='".$client."'";
     }
     if(!empty($status))
     {
     	$query .= "AND p.project_status_id='".$status."'";
     }*/
	$query = "CALL get_project_list_acc_user_proc('" . $_REQUEST['from_date'] . "', '" . $_REQUEST['to_date'] . "', '" . $client . "', '" . $status . "', '" . $logged_user_id . "');";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}

	if ($ref_type == "VIEW") {

		$return_data = array('status' => true, 'project_list' => $ret, 'qry' => $query);
		echo json_encode($return_data);
	} elseif ($ref_type = "DWNLD") {
		$objPHPExcel = new PHPExcel();
		// Set the active Excel worksheet to sheet 0 
		$objPHPExcel->setActiveSheetIndex(0);
		// Initialise the Excel row number 

		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'SL No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Provisional Number');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Client Name');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Project Status');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Project Cost');
		$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Created Date');
		$objPHPExcel->getActiveSheet()->setCellValue('G1', 'No Of Days');

		$styleArray = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '000000'),


			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);

		foreach (range('A', 'G') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}

		$rowCount = 2;
		$rowCount_new = 1;
		$day_diff = "";
		$existtempinid = array();
		foreach ($ret as  $value) {
			if ($value['project_no'] == "" || $value['project_no'] == null) {
				$project = $value['provisional_no'];
			} else {
				$project = $value['project_no'];
			}
			if ($value['project_status_id'] >= 13 || $value['day_diff'] != null) {
				$day_diff = $value['day_diff'];
			} else {
				$day_diff = "";
			}

			$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $project);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $value['client_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $value['project_status']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $value['project_cost']);
			$objPHPExcel->getActiveSheet()->setCellValue('F' . $rowCount, $value['created_ts']);
			$objPHPExcel->getActiveSheet()->setCellValue('G' . $rowCount, $day_diff);


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

		$file_name = 'project_list' . $today;
		$return_data =  array(
			'status' => true, 'file_name' => $file_name,
			'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
		);
		echo json_encode($return_data);
		exit;
	}
} else if ($action_type == "ADD_CUTOFF_DATE") {
	$eff_start_date = $_REQUEST['eff_start_date'];
	$eff_start_date = date('Y-m-d', strtotime($eff_start_date));
	$update_query = "UPDATE project_header SET po_cutoff_date='" . $eff_start_date . "',project_status_id=8 WHERE project_id='" . $_REQUEST['project_id_hidden'] . "'";
	$result_query = $db->query($update_query);
	if ($result_query) {

		$notification_query = "SELECT notification_project_started_created_by(" . $_REQUEST['project_id_hidden'] . "," . $logged_user_id . ")";
		$run_notification_query = $db->query($notification_query);
		$fetch_notification_query = mysqli_fetch_assoc($run_notification_query);


		$return_data = array('status' => true);
	} else {
		$return_data = array('status' => false);
	}
	echo json_encode($return_data);
} elseif ($action_type == "USER") {
	$query = "SELECT name,user_id,user_name from user_master WHERE is_active=1 and user_type<>'E'";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'user_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "EMPLOYEE") {
	$query = "SELECT name,user_id,user_name from user_master WHERE is_active=1 and user_type='E'";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'employee_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "GET_PROJECT_USER") {
	$today = date('d-m-Y');
	$ref_type = $_REQUEST['ref_type'];
	$project_id = $_REQUEST['project_id'];
	$ret_employee = $ret_user = array();
	$query = "SELECT um.user_id,um.name,um.emp_code,um.user_name,pu.supervisor_flag,ifnull(p.project_no,'') project_no,p.provisional_no,um.mobile_no_1  FROM project_user pu inner join user_master um ON pu.user_id=um.user_id  inner join project_header p on pu.project_header_id=p.project_id
  where pu.project_header_id='" . $project_id . "' and um.is_active='1' and pu.is_active='1' and um.user_type='E'";

	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$ret_employee[] = $data;
		}
	}
	$query1 = "SELECT um.user_id,um.name,um.user_name,pu.supervisor_flag,ifnull(p.project_no,'') project_no,p.provisional_no,um.mobile_no_1 FROM project_user pu inner join user_master um ON pu.user_id=um.user_id inner join project_header p on pu.project_header_id=p.project_id
  where pu.project_header_id='" . $project_id . "' and um.is_active='1' and pu.is_active='1' and um.user_type<>'E'";
	$result1 = $db->query($query1);
	if ($result1) {
		while ($data1 = mysqli_fetch_assoc($result1)) {
			$ret_user[] = $data1;
		}
	}
	if ($ref_type == 'VIEW') {
		$return_data  = array('status' => true, 'qry' => $query, 'employee_list' => $ret_employee, 'user_list' => $ret_user);
		echo json_encode($return_data);
	}

	if ($ref_type == 'DWNLD') {
		$objPHPExcel = new PHPExcel();
		// Set the active Excel worksheet to sheet 0 
		$objPHPExcel->setActiveSheetIndex(0);
		// Initialise the Excel row number 

		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'SL No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Full Name');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', 'User Name');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Mobile No.');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', 'User Role');



		$styleArray = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '000000'),


			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);

		foreach (range('A', 'E') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}

		$rowCount = 2;
		$rowCount_new = 1;
		$existtempinid = array();
		foreach ($ret_user as  $value) {
			if ($value['supervisor_flag'] == "u") {
				$user_role = 'User';
			} elseif ($value['supervisor_flag'] == "c") {
				$user_role = 'Co-ordinator';
			} else {
				$user_role = "Supervisor";
			}

			$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_new)->setRowHeight(-1);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_new++);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $value['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $value['user_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $value['mobile_no_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('E' . $rowCount, $user_role);




			$rowCount++;
		}
		$rowCount = $rowCount + 2;
		$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, 'SL No.');
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, 'Employee Code');
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, 'Full Name');
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, 'Mobile No.');
		$styleArray = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '000000'),


			)
		);

		$objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':D' . $rowCount)->applyFromArray($styleArray);

		foreach (range('A', 'D') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
		}
		$rowCount = $rowCount + 1;
		$rowCount_emp = 1;
		$existtempinid = array();
		foreach ($ret_employee as  $value2) {
			$objPHPExcel->getActiveSheet()->getRowDimension($rowCount_emp)->setRowHeight(-1);


			$objPHPExcel->getActiveSheet()->setCellValue('A' . $rowCount, $rowCount_emp++);
			$objPHPExcel->getActiveSheet()->setCellValue('B' . $rowCount, $value2['emp_code']);
			$objPHPExcel->getActiveSheet()->setCellValue('C' . $rowCount, $value2['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D' . $rowCount, $value2['mobile_no_1']);
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

		$file_name = 'project_user' . $today;
		$return_data =  array(
			'status' => true, 'file_name' => $file_name,
			'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
		);
		echo json_encode($return_data);
		exit;
	}
} elseif ($action_type == "ADD_EDIT_USER_PROJECT") {
	$project_id = $_REQUEST['project_id'];
	$get_user_id = $_REQUEST['user_id'];
	if ($_REQUEST['user_id']) {
		$get_user_id = implode(",", $get_user_id);
	}
	$get_employee_id = $_REQUEST['employee_id'];
	if ($get_employee_id) {
		$get_employee_id = implode(",", $get_employee_id);
	}
	$update_user_query = "update project_user SET is_active=0 WHERE project_header_id='" . $project_id . "' AND user_id NOT IN (" . $get_user_id . ")";
	$update_user = $db->query($update_user_query);
	$update_employee_query = "update project_user SET is_active=0 WHERE project_header_id='" . $project_id . "' AND user_id NOT IN (" . $get_employee_id . ")";
	$update_employee = $db->query($update_employee_query);
	if ($_REQUEST['user_id']) {
		foreach ($_REQUEST['user_id'] as $user_id) {
			$select_query = "SELECT project_user_id,supervisor_flag FROM project_user WHERE user_id=$user_id AND project_header_id=$project_id";
			$select_val = $db->query($select_query);
			$select_data = mysqli_fetch_assoc($select_val);
			if (!$select_data['project_user_id']) {
				$insert_query = "INSERT INTO project_user (project_header_id,user_id,created_by,created_ts) VALUES ('" . $project_id . "','" . $user_id . "','" . $logged_user_id . "',NOW())";
				$insert = $db->query($insert_query);
			} else {
				$update_user_query1 = "update project_user SET is_active=1,updated_by='" . $logged_user_id . "' WHERE project_header_id='" . $project_id . "' AND user_id='" . $user_id . "'";
				$update_user1 = $db->query($update_user_query1);
			}
		}
	}
	if ($_REQUEST['employee_id']) {
		foreach ($_REQUEST['employee_id'] as $employee_id) {
			$select_query1 = "SELECT project_user_id,supervisor_flag FROM project_user WHERE user_id=$employee_id AND project_header_id=$project_id";
			$select_val1 = $db->query($select_query1);
			$select_data1 = mysqli_fetch_assoc($select_val1);
			if (!$select_data1['project_user_id']) {
				$insert_query = "INSERT INTO project_user (project_header_id,user_id,created_by,created_ts) VALUES ('" . $project_id . "','" . $employee_id . "','" . $logged_user_id . "',NOW())";
				$insert = $db->query($insert_query);
			} else {
				$update_user_query2 = "update project_user SET is_active=1,updated_by='" . $logged_user_id . "' WHERE project_header_id='" . $project_id . "' AND user_id='" . $employee_id . "'";
				$update_user2 = $db->query($update_user_query2);
			}
		}
	}
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "supervisor") {
	$project_id = $_REQUEST['project_id'];
	$get_user_id = $_REQUEST['user_id'];
	$update_employee_query = "update project_user SET supervisor_flag='s' WHERE project_header_id='" . $project_id . "' AND user_id='" . $get_user_id . "'";
	$update_employee = $db->query($update_employee_query);
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "user") {
	$project_id = $_REQUEST['project_id'];
	$get_user_id = $_REQUEST['user_id'];
	$update_employee_query = "update project_user SET supervisor_flag='u' WHERE project_header_id='" . $project_id . "' AND user_id='" . $get_user_id . "'";
	$update_employee = $db->query($update_employee_query);
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "co_ordinator") {
	$project_id = $_REQUEST['project_id'];
	$get_user_id = $_REQUEST['user_id'];
	$update_employee_query = "update project_user SET supervisor_flag='c' WHERE project_header_id='" . $project_id . "' AND user_id='" . $get_user_id . "'";
	$update_employee = $db->query($update_employee_query);
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "ELEMENT") {
	$query = "SELECT im.item_id,im.item_name,im.uom_id,um.uom_name, IFNULL(MIN(pr.rate), 0) rate 
  from item_master im 
  inner join uom_master um ON um.uom_id=im.uom_id 
  left join purchase_rate pr ON im.item_id = pr.item_id and ((pr.eff_start_date <= curdate() and pr.eff_end_date >= curdate()) OR 
  (pr.eff_start_date <= curdate() and pr.eff_end_date = '0000-00-00'))
  WHERE im.is_active=1 and um.is_active=1 AND available_in IN ('P','B') GROUP BY im.item_id,im.item_name,pr.item_id;";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'element_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "GET_EVENT_DATA_APPROVE") {
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$element_details = $element_data = array();
	$query = "SELECT pe.qty,IFNULL(pe.remarks,'N/A') remarks,pe.item_id,pe.project_element_id,im.item_name,um.uom_name ,pe.initial_rate,pe.initial_price,ph.project_no,date_format( pre.delivery_datetime,'%d-%m-%Y') delivery_datetime,date_format( pre.start_date,'%d-%m-%Y') start_date,date_format( pre.end_date,'%d-%m-%Y') end_date ,pre.event_name
  FROM project_elements pe
inner join item_master im ON im.item_id=pe.item_id
inner join project_header ph ON pe.project_id=ph.project_id
inner join project_events pre ON pe.project_event_id=pre.project_event_id
inner join uom_master um ON im.uom_id=um.uom_id
WHERE um.is_active=1 and im.is_active=1  AND pe.project_id='" . $project_id . "' AND pe.project_event_id='" . $event_id . "' order by pe.project_element_id";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$element_details[] = $data;
		}
	}
	$return_data  = array('status' => true, 'element_details' => $element_details);
	echo json_encode($return_data);
} elseif ($action_type == "GET_EVENT_DATA") {
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$element_details = $element_data = array();
	$query = "SELECT sum(pe.qty) as qty, IFNULL(pe.remarks, 'N/A') remarks, pe.item_id, pe.project_element_id, im.item_name, um.uom_name, pe.initial_rate, pe.initial_price, ph.project_no, date_format( pre.delivery_datetime, '%d-%m-%Y') delivery_datetime, date_format(pre.start_date, '%d-%m-%Y') start_date, date_format(pre.end_date, '%d-%m-%Y') end_date, pre.event_name, 
		(IFNULL(
		(SELECT SUM(ppd.qty) FROM prov_po_header pph
		INNER JOIN prov_po_detail ppd ON pph.prov_po_id = ppd.prov_po_id 
		WHERE pph.project_id = pe.project_id AND pph.project_event_id = pe.project_event_id AND ppd.item_id = pe.item_id AND pph.approve_flag != 'R'
		), 0) + 
		IFNULL(
		(SELECT SUM(pd.qty) FROM po_header ph
		INNER JOIN po_detail pd ON ph.po_id = pd.po_id 
		WHERE ph.project_id = pe.project_id AND ph.project_event_id = pe.project_event_id AND pd.item_id = pe.item_id AND ph.approve_flag != 'R'
		), 0) + 
		IFNULL(
		(SELECT SUM(red.qty) FROM requisition_header reh
		INNER JOIN requisition_detail red ON red.requisition_id = reh.requisition_id
		WHERE reh.project_id = pe.project_id AND reh.project_event_id = pe.project_event_id AND red.item_id = pe.item_id
		), 0)
		) ordered_qty
	FROM project_elements pe
	INNER JOIN item_master im ON im.item_id = pe.item_id
	INNER JOIN project_header ph ON pe.project_id = ph.project_id
	INNER JOIN project_events pre ON pe.project_event_id = pre.project_event_id
	INNER JOIN uom_master um ON im.uom_id = um.uom_id
	WHERE um.is_active = 1 AND im.is_active = 1 AND 
	pe.project_id = '" . $project_id . "' AND pe.project_event_id = '" . $event_id . "' 
	GROUP BY pe.item_id 
	ORDER BY pe.project_element_id;";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$element_details[] = $data;
		}
	}
	$query = "CALL availability_event_item_proc($project_id,$event_id,'0')";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$element_data[] = $data;
		}
	}

	$return_data  = array('status' => true, 'element_details' => $element_details, 'element_data' => $element_data);
	echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_ELEMENT_PROJECT") {
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$count_elements_id = $_REQUEST['count_elements_id'];
	foreach ($_REQUEST['element_id'] as $k => $element_id) {
		if ($_REQUEST['project_element_id'][$k]) {
			$update_query = "UPDATE project_elements SET item_id='" . $_REQUEST['element_id'][$k] . "',uom_id='" . $_REQUEST['uom_id'][$k] . "',qty='" . $_REQUEST['qty'][$k] . "',initial_rate='" . $_REQUEST['rate'][$k] . "',initial_price='" . $_REQUEST['price_hidden'][$k] . "',updated_by='" . $logged_user_id . "' WHERE project_element_id='" . $_REQUEST['project_element_id'][$k] . "'";
			$update_result = $db->query($update_query);
		} else {
			$insert_query = "INSERT INTO project_elements (project_id,project_event_id,item_id,uom_id,qty,initial_rate,initial_price,created_by,created_ts) values ('" . $project_id . "','" . $event_id . "','" . $_REQUEST['element_id'][$k] . "','" . $_REQUEST['uom_id'][$k] . "','" . $_REQUEST['qty'][$k] . "','" . $_REQUEST['rate'][$k] . "','" . $_REQUEST['price_hidden'][$k] . "','" . $logged_user_id . "',NOW())";
			$insert_result = $db->query($insert_query);
		}
	}
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_ELEMENT_PROJECT_APPROVE") {
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];

	foreach ($_REQUEST['project_element_id'] as $k => $element_id) {
		if ($_REQUEST['project_element_id'][$k]) {
			$update_query = "UPDATE project_elements SET qty='" . $_REQUEST['qty'][$k] . "',initial_price='" . $_REQUEST['price_hidden'][$k] . "',updated_by='" . $logged_user_id . "',remarks='" . $_REQUEST['remarks'][$k] . "' WHERE project_element_id='" . $_REQUEST['project_element_id'][$k] . "'";
			$update_result = $db->query($update_query);
		}
	}
	$return_data  = array('status' => true);
	echo json_encode($return_data);
} elseif ($action_type == "REMOVE_DIV") {
	$project_element_id = $_REQUEST['project_element_id'];
	$delete_query = "DELETE FROM project_elements WHERE project_element_id='" . $project_element_id . "'";
	$delete_result = $db->query($delete_query);
	if ($delete_result) {
		$return_data  = array('status' => true);
	}
	echo json_encode($return_data);
} elseif ($action_type == "PROJ_STATUS") {
	$query = "SELECT * FROM project_status";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$ret[] = $data;
		}
	}

	$return_data  = array('status' => true, 'status_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "VENDOR") {
	$query = "SELECT vendor_id,vendor_name,vendor_code,state_id FROM vendor_master WHERE is_active=1";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'vendor_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "Bank") {
	$query = "SELECT bank_id,bank_name,bank_head_office_address FROM bank_master";
	//echo $query;die();
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$return_data  = array('status' => true, 'vendor_list' => $ret);
	echo json_encode($return_data);
} elseif ($action_type == "COMPANY") {
	$project_id = $_REQUEST['project_id'];
	$query = "SELECT company_id,company_name,state_id FROM company_master WHERE is_active=1";
	$result = $db->query($query);
	while ($data = mysqli_fetch_assoc($result)) {
		$ret[] = $data;
	}
	$qry = "SELECT company_id AS selcted_company FROM project_header WHERE project_id='" . $project_id . "'";
	$res = $db->query($qry);
	$res_company = mysqli_fetch_assoc($res);
	$return_data  = array('status' => true, 'company_list' => $ret, 'slected' => $res_company);
	echo json_encode($return_data);
} elseif ($action_type == "GET_ELEMENT_DATA_FOR_PO") {
	$today = date('Y-m-d');
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$item_id = $_REQUEST['item_id'];
	$element_data = array();
	$query = "CALL availability_event_item_proc($project_id,$event_id,'" . $item_id . "')";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$element_data[] = $data;
		}
	}
	$db->next_result();

	$query_evnt = "SELECT ph.project_no, date_format(pe.delivery_datetime, '%d-%m-%Y') delivery_datetime, date_format(pe.start_date, '%d-%m-%Y') start_date, date_format(pe.end_date, '%d-%m-%Y') end_date ,pe.event_name
  FROM project_header ph
  INNER JOIN project_events pe ON ph.project_id=pe.project_id
  WHERE ph.project_id='" . $project_id . "' AND pe.project_event_id='" . $event_id . "'";
	$result_evnt = $db->query($query_evnt);
	$evnt_details = array();
	if ($result_evnt) {
		$evnt_details = mysqli_fetch_assoc($result_evnt);
	}
	$return_data  = array('status' => true, 'evnt_details' => $evnt_details, 'element_data' => $element_data);
	echo json_encode($return_data);
} elseif ($action_type == "GET_ELEMENT_PRICE_VENDOR") {
	$today = date('Y-m-d');
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$item_id = $_REQUEST['item_id'];
	$vendor_id = $_REQUEST['vendor_id'];
	$price_data = array();
	$query = "SELECT im.item_id,pr.rate,tm.tax_percentage,tm.cgst_percentage,tm.sgst_percentage FROM item_master im 
inner JOIN hsn_sac_master hsm ON im.hsn_sac_master_id=hsm.hsn_sac_id AND hsm.is_active=1
inner JOIN tax_master tm ON tm.tax_id=hsm.tax_id AND tm.is_active=1 AND tm.eff_start_date<=CURDATE()
left JOIN purchase_rate pr ON im.item_id=pr.item_id AND pr.vendor_id='" . $vendor_id . "' and pr.item_id IN($item_id) AND 
(pr.eff_start_date<=CURDATE() OR pr.eff_end_date<=curdate() OR pr.eff_end_date='0000-00-00' OR pr.eff_start_date<=curdate()<=pr.eff_end_date)
WHERE  im.item_id IN($item_id) 
group by im.item_id";
	$result = $db->query($query);
	if ($result) {
		while ($data = mysqli_fetch_assoc($result)) {
			$price_data[] = $data;
		}
	}

	$return_data  = array('status' => true, 'query' => $query, 'price_data' => $price_data);
	echo json_encode($return_data);
} elseif ($action_type == "ADD_EDIT_PO_PROJECT") {
	//echo json_encode($_REQUEST); exit();
	$total_amount = $total_cgst = $total_sgst = $total_igst = $total_taxable_amount = 0;
	$item_price = $_REQUEST['price'];
	$item_tot_price = array_sum($item_price);
	$project_id = $_REQUEST['project_id'];
	$event_id = $_REQUEST['event_id'];
	$vendor_id = $_REQUEST['vendor_id'];
	$company_id = $_REQUEST['company_id'];
	$po_date = $_REQUEST['po_date'];
	$po_date = date('Y-m-d', strtotime($po_date));
	$gst_flag = $_REQUEST['gst_flag'];

	$item_string = "";
	$col_delm = "$@";
	$row_delm = "|#|";
	foreach ($_REQUEST['item_id_hidden'] as $k => $item_id) {
		$item_string .= intval($item_id) . $col_delm . $_REQUEST['uom_id_hidden'][$k] . $col_delm . $_REQUEST['qty'][$k] . $col_delm . $_REQUEST['item_price'][$k] . $col_delm . $_REQUEST['cgst_hid'][$k] . $col_delm . $_REQUEST['sgst_hid'][$k] . $col_delm . $_REQUEST['igst_hid'][$k] . $row_delm;
	}
	$item_string = rtrim($item_string, $row_delm);

	$sql_add_po = "CALL add_po_prov_po_proc('" . $vendor_id . "', '" . $company_id . "', '" . $project_id . "', '" . $event_id . "', '" . $_REQUEST['po_date'] . "', '" . $db->escape_string($_REQUEST['remarks']) . "', '" . $_REQUEST['submit_type'] . "', b'" . $gst_flag . "', '" . $item_string . "', '" . $logged_user_id . "');";
	//echo json_encode($sql_add_po); exit();
	$rs_query = $db->query($sql_add_po);
	$po_result = mysqli_fetch_assoc($rs_query);
	$return_data  = array('status' => true, 'po_result' => $po_result, 'qry' => $sql_add_po);
	/*
	if($_REQUEST['submit_type']=="s"){
					$select_sql="SELECT generate_po_no_func('".$company_id."') AS po_number";
					$select_po_number_query=$db->query($select_sql);
					$select_po_number=mysqli_fetch_assoc($select_po_number_query);
					$po_number=$select_po_number['po_number'];
					$insert_query="INSERT INTO po_header (po_number,project_id,project_event_id,vendor_id,company_id,total_amount,remarks,po_date,created_by,created_ts) VALUES ('".$po_number."','".$project_id."','".$event_id."','".$vendor_id."','".$company_id."','".$item_tot_price."','".$_REQUEST['remarks']."','".$po_date."','".$logged_user_id."',NOW())";
					$insert_sql=$db->query($insert_query);
					$po_header_id=$db->insert_id();
		foreach($_REQUEST['item_id_hidden'] as $k=>$item_id){
			$select_sql_cnt="SELECT COUNT(purchase_rate_id) AS cnt FROM purchase_rate WHERE vendor_id='".$vendor_id."' AND item_id='".$item_id."'";
					$select_query_cnt=$db->query($select_sql_cnt);
					$purchase_rate_id=mysqli_fetch_assoc($select_query_cnt);
					$purchase_rate_id_cnt=$purchase_rate_id['cnt'];
					if($purchase_rate_id_cnt==0){
					$insert_query_purchase_rate="INSERT INTO purchase_rate (item_id,uom_id,qty,vendor_id,rate,eff_start_date,created_by,created_ts) VALUES ('".$item_id."','".$_REQUEST['uom_id_hidden'][$k]."',1,'".$vendor_id."','".$_REQUEST['item_price'][$k]."',CURDATE(),'".$logged_user_id."',NOW())";
					$insert_sql_purchase_rate=$db->query($insert_query_purchase_rate);
					}
			$amount=$_REQUEST['price'][$k]*$_REQUEST['qty'][$k];
			$total_amount+=$_REQUEST['price'][$k];
			$total_cgst+=$_REQUEST['cgst_hid'][$k];
			$total_sgst+=$_REQUEST['sgst_hid'][$k];
			$total_igst+=$_REQUEST['igst_hid'][$k];
			$total_taxable_amount+=$amount;
			$insert_query_det="INSERT INTO po_detail (po_id,item_id,uom_id,qty,rate,cgst_amt,sgst_amt,igst_amt,taxable_amt,amount,created_by,created_ts) 
			VALUES ('".$po_header_id."','".$item_id."','".$_REQUEST['uom_id_hidden'][$k]."','".$_REQUEST['qty'][$k]."','".$_REQUEST['item_price'][$k]."','".$_REQUEST['cgst_hid'][$k]."','".$_REQUEST['sgst_hid'][$k]."','".$_REQUEST['igst_hid'][$k]."','".$_REQUEST['price_hid'][$k]."','".$_REQUEST['price'][$k]."','".$logged_user_id."',NOW())";
			$insert_sql_det=$db->query($insert_query_det);	
		}
		$update_query="UPDATE po_header SET total_amount='".$total_amount."',total_taxable_amt='".$total_taxable_amount."',total_cgst_amt='".$total_cgst."',total_sgst_amt='".$total_sgst."',total_igst_amt='".$total_igst."' WHERE po_id='".$po_header_id."' AND project_id='".$project_id."' AND project_event_id='".$event_id."'";
		$update_sql=$db->query($update_query);
		
		$notification_insert_ev="SELECT notification_po_genarate_admin_fun($project_id,$vendor_id,$company_id,$logged_user_id,'po_id',$po_header_id)";	
		$run_notification_ev = $db->query($notification_insert_ev);
		
	}elseif($_REQUEST['submit_type']=="c"){
		$select_sql="SELECT generate_prov_no_func('".$company_id."','".$project_id."') AS po_number";
		$select_po_number_query=$db->query($select_sql);
		$select_po_number=mysqli_fetch_assoc($select_po_number_query);
		$po_number=$select_po_number['po_number'];
			$insert_query="INSERT INTO prov_po_header (prov_po_number,project_id,project_event_id,vendor_id,company_id,total_amount,remarks,created_by,created_ts) VALUES ('".$po_number."','".$project_id."','".$event_id."','".$vendor_id."','".$company_id."','".$item_tot_price."','".$_REQUEST['remarks']."','".$logged_user_id."',NOW())";
			$insert_sql=$db->query($insert_query);
			$po_header_id=$db->insert_id();
				foreach($_REQUEST['item_id_hidden'] as $k=>$item_id){
					$select_sql_cnt="SELECT COUNT(purchase_rate_id) AS cnt FROM purchase_rate WHERE vendor_id='".$vendor_id."' AND item_id='".$item_id."'";
					$select_query_cnt=$db->query($select_sql_cnt);
					$purchase_rate_id=mysqli_fetch_assoc($select_query_cnt);
					$purchase_rate_id_cnt=$purchase_rate_id['cnt'];
					if($purchase_rate_id_cnt==0){
					$insert_query_purchase_rate="INSERT INTO purchase_rate (item_id,uom_id,qty,vendor_id,rate,eff_start_date,created_by,created_ts) VALUES ('".$item_id."','".$_REQUEST['uom_id_hidden'][$k]."',1,'".$vendor_id."','".$_REQUEST['item_price'][$k]."',CURDATE(),'".$logged_user_id."',NOW())";
					$insert_sql_purchase_rate=$db->query($insert_query_purchase_rate);
					}
					$amount=$_REQUEST['price'][$k]*$_REQUEST['qty'][$k];
					$total_amount+=$_REQUEST['price'][$k];
					$total_cgst+=$_REQUEST['cgst_hid'][$k];
					$total_sgst+=$_REQUEST['sgst_hid'][$k];
					$total_igst+=$_REQUEST['igst_hid'][$k];
				$total_taxable_amount+=$amount;
				$insert_query_det="INSERT INTO prov_po_detail (prov_po_id,item_id,uom_id,qty,rate,cgst_amt,sgst_amt,igst_amt,after_tax_amt,amount,created_by,created_ts) VALUES 
				('".$po_header_id."','".$item_id."','".$_REQUEST['uom_id_hidden'][$k]."','".$_REQUEST['qty'][$k]."','".$_REQUEST['item_price'][$k]."','".$_REQUEST['cgst_hid'][$k]."','".$_REQUEST['sgst_hid'][$k]."','".$_REQUEST['igst_hid'][$k]."','".$_REQUEST['price_hid'][$k]."','".$_REQUEST['price'][$k]."','".$logged_user_id."',NOW())";
				$insert_sql_det=$db->query($insert_query_det);
	}
	$update_query="UPDATE prov_po_header SET total_taxable_amt='".$total_taxable_amount."',total_cgst='".$total_cgst."',total_sgst='".$total_sgst."',total_igst='".$total_igst."',total_amount='".$total_amount."' WHERE prov_po_id='".$po_header_id."' AND project_id='".$project_id."' AND project_event_id='".$event_id."'";
		$update_sql=$db->query($update_query);
		
	$notification_insert_ev="SELECT notification_po_genarate_admin_fun($project_id,$vendor_id,$company_id,$logged_user_id,'prov_po_id',$po_header_id)";	
	$run_notification_ev = $db->query($notification_insert_ev);	
	}
	*/
	//$return_data  = array('status' => true,'po_number'=>$po_number);
	echo json_encode($return_data);
} elseif ($action_type == "CLOSE_PROJECT") {
	$project_id = $_REQUEST['project_id'];
	$today = date('Y-m-d');
	$update_sql = "UPDATE project_header SET project_status_id='13', completed_date='" . $today . "' WHERE project_id='" . $project_id . "'";
	$update_query = $db->query($update_sql);
	$update_event_sql = "UPDATE project_events SET events_status_id='13' WHERE project_id='" . $project_id . "'";
	$update_event_query = $db->query($update_event_sql);
	$notification_insert_ev = "SELECT notification_close_project_fun($project_id,$logged_user_id)";
	$run_notification_ev = $db->query($notification_insert_ev);
	if ($update_query) {
		$return_data  = array('status' => true);
	}
	echo json_encode($return_data);
} elseif ($action_type == "REOPEN_PROJECT") {
	$project_id = $_REQUEST['project_id'];
	$update_sql = "UPDATE project_header SET project_status_id='10' WHERE project_id='" . $project_id . "'";
	$update_query = $db->query($update_sql);
	$update_event_sql = "UPDATE project_events SET events_status_id='10' WHERE project_id='" . $project_id . "'";
	$update_event_query = $db->query($update_event_sql);
	if ($update_query) {
		$return_data  = array('status' => true);
	}
	echo json_encode($return_data);
}
