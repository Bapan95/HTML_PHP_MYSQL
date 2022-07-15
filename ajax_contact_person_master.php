<?php
require_once("../lib/config.php");
require_once("../lib/constants.php");
//require_once('../Classes/PHPExcel.php');
//require_once('../Classes/PHPExcel/IOFactory.php');
$logged_user_id = my_session('user_id');
if(isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')){
$logged_user_id = $_REQUEST['user_id'];
}
$action_type = $_REQUEST['action_type'];
$upd_brn_id = $_REQUEST['upd_brn_id'];

$role_id =  my_session('role_id');
$return_data  = array();


$list_r_id_ai = $_REQUEST['list_r_id_ai'];

if($action_type=="PERSON_LISTING")
{
  $query = "SELECT contact_person_id,branch_name,contact_person_name,designation,email_id,contact_number,cm.branch_id FROM contact_person_master cm INNER JOIN bank_branch_master bm ON cm.branch_id=bm.branch_id   ORDER BY contact_person_name";
  $result = $db->query($query);
  while($data=mysqli_fetch_assoc($result))
  {
  	$ret[] = $data;
	}
$return_data  = array('status' => true, 'person_list'=>$ret, 'query'=>$query);
echo json_encode($return_data);
}






else if($action_type=="CATEGORY"){
	//$publication_id = intval($_POST['publication_id']);
	
	$category_list = array();
	$query = "SELECT branch_id, branch_name FROM bank_branch_master";
	//echo $query;die;
	$result = $db->query($query);
	if($result->num_rows > 0){
		while($row = mysqli_fetch_assoc($result)){
			$category_list[] = $row;
		}
		$return_data  = array('status' => true, 'category_list'=>$category_list, 'qry' => $query);
	}
	else{
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}


else if($action_type=="ADD_EDIT_BANK")
{ 
	$bank_name = $_REQUEST['bank_name'];
	
	$b_address = $_REQUEST['b_address'];
	
	$chk_code = "SELECT COUNT(bank_id) AS cnt FROM bank_master WHERE bank_name='".$bank_name."'  AND bank_id!='".$upd_brn_id."'  ";
	//echo $chk_code;die;
	$chk_res = $db->query($chk_code);
	 $chk_detail = mysqli_fetch_assoc($chk_res);
		$chk_detail['cnt'];
	  if($chk_detail['cnt']>0)
	  {
		
		$id = 0;
		
	  }    
	else{
	if($upd_brn_id=="" || $upd_brn_id==" ")
	{ 
	 //$m_no = "select generateSerialNoRNS('BRND',1)";
	 // $qry_run = $db->query($m_no);
	 // $brand_code = mysqli_fetch_assoc($qry_run);
	  //$brand_code = $brand_code["generateSerialNoRNS('BRND',1)"];
	  $query =  "INSERT INTO bank_master (bank_name,bank_head_office_address,created_by,created_time) VALUES ('".$bank_name."','".$b_address."','".$logged_user_id."',now())";
	//   echo $query;die;
	 $result = $db->query($query);
	  if($result)
	  {
		$msg = "Inserted sucessfully !";
		$id = 1;
	  }
	
}
	else
	{
	  $upd_query = "UPDATE bank_master SET 
		
		bank_name='".$bank_name."',	  
		bank_head_office_address='".$b_address."',	  
		updated_by='".$logged_user_id."',
		updated_time=now() 
	   WHERE bank_id='".$upd_brn_id."'";
	   $res = $db->query($upd_query);
	   if($res)
	   {
		$msg = "Updated sucessfully !";
			$id=2;
	   }
	   else
	   {
		$msg = "Try again !";
	   }
	   

	}
}

	$return_data = array('status' => true,'msg'=>$msg,'qry'=>$query,'id'=>$id);
		echo json_encode($return_data);
}
else if($action_type=="BANK_LISTING")
{
  $query = "SELECT bank_id,bank_name,bank_head_office_address FROM bank_master bm  ORDER BY bank_name";
  $result = $db->query($query);
  while($data=mysqli_fetch_assoc($result))
  {
  	$ret[] = $data;
	}
$return_data  = array('status' => true, 'bank_list'=>$ret, 'query'=>$query);
echo json_encode($return_data);
}
else if($action_type=="EDIT_BANK")
{
$upd_brn_id = $_REQUEST['bank_id'];
   $query = "SELECT bank_id,bank_name,bank_head_office_address FROM bank_master  WHERE bank_id='".$upd_brn_id."'";
 $result = $db->query($query);
 if($result)
 {
   $ret = mysqli_fetch_assoc($result);
   $return_data  = array('status' => true,'cat_upd_list'=>$ret, 'query'=>$query);
 }
echo json_encode($return_data);

}
else if($action_type=="BANK"){
	//$publication_id = intval($_POST['publication_id']);
	
	$category_list = array();
	$query = "SELECT DISTINCT bank_id, bank_name FROM bank_master";
	//echo $query;die;
	$result = $db->query($query);
	if($result->num_rows > 0){
		while($row = mysqli_fetch_assoc($result)){
			$category_list[] = $row;
		}
		$return_data  = array('status' => true, 'category_list'=>$category_list, 'qry' => $query);
	}
	else{
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}

else if($action_type=="ADD_EDIT_BRANCH")
{ 
	$category_id = $_REQUEST['category_id'];
	
	$branch_name = $_REQUEST['branch_name'];
	//$s_code = $_REQUEST['s_code'];
	$branch_address = $_REQUEST['branch_address'];
	$b_phone_no = $_REQUEST['b_phone_no'];
	$b_manager_name = $_REQUEST['b_manager_name'];
	$account_no = $_REQUEST['account_no'];
	$account_no_hidden[] = $_REQUEST['account_no_hidden'];
$account_no_hidden_count = count($_REQUEST['account_no']);
$account_no_hidden_count_detail = count($_REQUEST['account_no_hidden']);

//echo $account_no_hidden_count_detail; die;

    $od_limit = $_REQUEST['od_limit'];
    $hp_limit = $_REQUEST['hp_limit'];
    $lc_limit = $_REQUEST['lc_limit'];
    $pbg_limit = $_REQUEST['pbg_limit'];
    $apg_limit = $_REQUEST['apg_limit'];
    $bg_limit = $_REQUEST['bg_limit'];




	//print_r($_REQUEST);die;
	$chk_code = "SELECT COUNT(branch_id) AS cnt FROM bank_branch_master WHERE branch_name='".$branch_name."' AND bank_id='".$category_id."' AND branch_id!='".$upd_brn_id."'  ";
	//echo $chk_code;die;
	$chk_res = $db->query($chk_code);
	 $chk_detail = mysqli_fetch_assoc($chk_res);
		$chk_detail['cnt'];
	  if($chk_detail['cnt']>0)
	  {
		
		$id = 0;
		
	  }    
	else if($upd_brn_id=="" || $upd_brn_id==" ")
	{  
		$query =  "INSERT INTO bank_branch_master (bank_id,branch_name,branch_address,branch_phone,branch_manager_name,created_by,created_time) VALUES ('".$category_id."','".$branch_name."','".$branch_address."','".$b_phone_no."','".$b_manager_name."','".$logged_user_id."',now())";
		$result = $db->query($query);
	
		$last_insert_id = $db->insert_id();
	
		for ($y = 0; $y < $account_no_hidden_count; $y++) { 
		  $query2 = "insert into acc_master (branch_id, account_no, od_limit, hp_imit, lc_limit, pbg_limit, apg_limit, bg_limit, created_time) values ('".$last_insert_id."','".$account_no[$y]."','".$od_limit[$y]."','".$hp_limit[$y]."','".$lc_limit[$y]."','".$pbg_limit[$y]."','".$apg_limit[$y]."','".$bg_limit[$y]."',NOW())";
		  $result1 = $db->query($query2);
		}  
		if($result)
		{	      
		  $msg = "Inserted sucessfully !";
		  $id = 1;        
		}
		else
		{      
		  $msg = "Try again !";
		}
  
  
	  } 
	//   else
	// {
	//   $upd_query = "UPDATE bank_branch_master SET 
	// 	bank_id='".$category_id."',
	// 	branch_name='".$branch_name."',	  
	// 	branch_address='".$branch_address."',
	// 	branch_phone='".$b_phone_no."',	
	// 	branch_manager_name='".$b_manager_name."',	  
	// 	updated_by='".$logged_user_id."',
	// 	updated_time=now() 
	//    WHERE branch_id='".$upd_brn_id."'";
	//    $res = $db->query($upd_query);
	//    if($res)
	//    {
	// 	$msg = "Updated sucessfully !";
	// 		$id=2;
	//    }
	//    else
	//    {
	// 	$msg = "Try again !";
	//    }
	   

	// }
	else
    { 
        $qry_details = "UPDATE bank_branch_master SET 
		bank_id='".$category_id."',
		branch_name='".$branch_name."',	  
		branch_address='".$branch_address."',
		branch_phone='".$b_phone_no."',	
		branch_manager_name='".$b_manager_name."',	  
		updated_by='".$logged_user_id."',
		updated_time=now() 
	   WHERE branch_id='".$upd_brn_id."'";
      $res_details = $db->query($qry_details);
      if($res_details)
      {
          for ($y = 0; $y < $account_no_hidden_count_detail; $y++)
          {       
            if(trim($_REQUEST['account_no_hidden'][$y]) == "")
            {
            $qry_partner = "insert into acc_master (branch_id, account_no, od_limit, hp_imit, lc_limit, pbg_limit, apg_limit, bg_limit, created_time) values ('".$upd_brn_id."','".$account_no[$y]."','".$od_limit[$y]."','".$hp_limit[$y]."','".$lc_limit[$y]."','".$pbg_limit[$y]."','".$apg_limit[$y]."','".$bg_limit[$y]."',NOW())";
              $res_partner = $db->query($qry_partner);
            }
            else
            {
              $qry_partner = "UPDATE acc_master SET 
              branch_id='".$upd_brn_id."',
              account_no='".$account_no[$y]."',
			  od_limit='".$od_limit[$y]."',
			  hp_imit='".$hp_limit[$y]."', 
			  lc_limit='".$lc_limit[$y]."', 
			  pbg_limit='".$pbg_limit[$y]."', 
			  apg_limit='".$apg_limit[$y]."', 
			  bg_limit='".$bg_limit[$y]."', 
			  updated_time=NOW()
               
              WHERE acc_master_id='".$_REQUEST['account_no_hidden'][$y]."'";
              $res_partner = $db->query($qry_partner);
            }  
            //print_r($account_no_hidden);			
            //echo $account_no_hidden_count_detail;
			//$qry_partner;
			//echo $_REQUEST['account_no_hidden'][$y];
			
          }
		  //print_r($account_no_hidden);			
          $msg = "Updated sucessfully !";
          $id=2;
      }
      else
      {
        $msg = "Try again !";
      }    

    }
	 
	


	$return_data = array('status' => true,'msg'=>$msg,'qry'=>$qry_partner,'id'=>$id);
		echo json_encode($return_data);
}

else if($action_type=="BRANCH_LISTING")
{
  $query = "SELECT branch_name,branch_address,swift_code,branch_id,bank_name,branch_phone,branch_manager_name FROM bank_branch_master bbm INNER JOIN bank_master bm ON bbm.bank_id=bm.bank_id   ORDER BY bm.bank_name";
  $result = $db->query($query);
  while($data=mysqli_fetch_assoc($result))
  {
  	$ret[] = $data;
	}
$return_data  = array('status' => true, 'branch_list'=>$ret, 'query'=>$query);
echo json_encode($return_data);
}
else if($action_type=="EDIT_BRANCH")
{
$upd_brn_id = $_REQUEST['branch_id'];
   $query = "SELECT branch_name,branch_address,swift_code,branch_id,bank_name,bbm.bank_id,branch_phone,branch_manager_name FROM bank_branch_master bbm INNER JOIN bank_master bm ON bbm.bank_id=bm.bank_id  WHERE branch_id='".$upd_brn_id."'";
 $result = $db->query($query);
 $ret = mysqli_fetch_assoc($result);
 
 $query2 = "SELECT * FROM acc_master WHERE branch_id='".$upd_brn_id."'";
 $result2 = $db->query($query2);
 while($ret2 = mysqli_fetch_assoc($result2)){
	 $acc_deail[] = $ret2;
 }
 /*if($result)
 {
   $ret = mysqli_fetch_assoc($result);
   $return_data  = array('status' => true,'cat_upd_list'=>$ret, 'query'=>$query);
 }*/
 $return_data  = array('status' => true,'cat_upd_list'=>$ret, 'acc_detail'=>$acc_deail, 'query'=>$query2);
echo json_encode($return_data);

}
///////////////////////////////////contact_person start here//////////////////////

else if($action_type=="bank_populate"){
	//$publication_id = intval($_POST['publication_id']);
	
	$category_list = array();
	$query = "SELECT  bank_id, bank_name FROM bank_master";
	//echo $query;die;
	$result = $db->query($query);
	if($result->num_rows > 0){
		while($row = mysqli_fetch_assoc($result)){
			$category_list[] = $row;
		}
		$return_data  = array('status' => true, 'bank_list'=>$category_list, 'qry' => $query);
	}
	else{
		$return_data  = array('status' => false, 'qry' => $query);
	}
	echo json_encode($return_data);
}

elseif($action_type == 'LIST_CATEGORIES'){
	$bank_id = $_REQUEST['bank_id'];

	$query = "SELECT branch_id, branch_name FROM bank_branch_master WHERE  bank_id ='".$bank_id."'";
	//echo $query;die;
	$result = $db->query($query);
  
	 while($data=mysqli_fetch_assoc($result)){
		$ret[] = $data;
	 }
		$return_data  = array('status' => true, 'branch_list'=>$ret );
		echo json_encode($return_data);
	  }

////contact_master submit here

else if($action_type=="ADD_EDIT_PERSON")
{ 
	$person_name = $_REQUEST['person_name'];
	
	$category_id = $_REQUEST['category_id'];
	$bank = $_REQUEST['bank'];

	$designation = $_REQUEST['designation'];

	$email = $_REQUEST['email'];

	$phno = $_REQUEST['phno'];
	//print_r($_REQUEST);die;
// if(trim($person_name)=="")
	// {
	// 	$id = 3;
	// 	$msg = "Person Name Can not be Empty";
	// 	$return_data = array('status' => true, 'code' =>'', 'msg'=>$msg,'id'=>$id);
	// 	echo json_encode($return_data);
	// 	die();
	// }  
	$chk_code = "SELECT COUNT(contact_person_id) AS cnt FROM contact_person_master WHERE contact_person_name='".$person_name."' AND contact_person_id!='".$upd_brn_id."'  ";
	//echo $chk_code;die;
	$chk_res = $db->query($chk_code);
	 $chk_detail = mysqli_fetch_assoc($chk_res);
		$chk_detail['cnt'];
	  if($chk_detail['cnt']>0)
	  {
		
		$id = 0;
		
	  }    
	else{
	if($upd_brn_id=="" || $upd_brn_id==" ")
	{ 
	 //$m_no = "select generateSerialNoRNS('BRND',1)";
	 // $qry_run = $db->query($m_no);
	 // $brand_code = mysqli_fetch_assoc($qry_run);
	  //$brand_code = $brand_code["generateSerialNoRNS('BRND',1)"];
	  $query =  "INSERT INTO contact_person_master (branch_id,contact_person_name,designation,email_id,contact_number,created_by,created_time) VALUES ('".$category_id."','".$person_name."','".$designation."','".$email."','".$phno."','".$logged_user_id."',now())";
	  //echo $query;die;
	 $result = $db->query($query);
	  if($result)
	  {
		$msg = "Inserted sucessfully !";
		$id = 1;
	
	  }
	
}
	else
	{
	  $upd_query = "UPDATE contact_person_master SET 
		branch_id='".$category_id."',
		contact_person_name='".$person_name."',	  
		designation='".$designation."',	  
		email_id='".$email."',
		contact_number='".$phno."',	  
		updated_by='".$logged_user_id."',
		updated_time=now() 
	   WHERE contact_person_id='".$upd_brn_id."'";
	   $res = $db->query($upd_query);
	   if($res)
	   {
		$msg = "Updated sucessfully !";
			$id=2;
	   }
	   else
	   {
		$msg = "Try again !";
	   }
	   

	}
}

	$return_data = array('status' => true,'code'=>$brand_code,'msg'=>$msg,'qry'=>$query,'id'=>$id);
		echo json_encode($return_data);
}
else if($action_type=="EDIT_PERSON")
{
$upd_brn_id = $_REQUEST['person_id'];
  // $query = "SELECT contact_person_id,branch_name,contact_person_name,designation,email_id,contact_number,cm.branch_id FROM contact_person_master cm INNER JOIN bank_branch_master bm ON cm.branch_id=bm.branch_id WHERE contact_person_id='".$upd_brn_id."'";
  $query= "SELECT contact_person_id,bank_name,bm.bank_id,branch_name,contact_person_name,designation,email_id,contact_number,cm.branch_id FROM contact_person_master cm INNER JOIN bank_branch_master bm ON cm.branch_id=bm.branch_id INNER JOIN bank_master ON bank_master.bank_id = bm.bank_id WHERE contact_person_id='".$upd_brn_id."'";
 $result = $db->query($query);
 if($result)
 {
   $ret = mysqli_fetch_assoc($result);
   $return_data  = array('status' => true,'cat_upd_list'=>$ret, 'query'=>$query);
 }
echo json_encode($return_data);

}

elseif($action_type=="ACCOUNT_INACTIVE"){
	$acc_id = $_REQUEST['acc_id'];
	$query="UPDATE acc_master SET acc_status='0' WHERE acc_master_id = '".$acc_id."'";
	$result = $db->query($query);
	$return_data  = array('status' => true);
	echo json_encode($return_data);
}

elseif($action_type=="ACCOUNT_ACTIVE"){
	$acc_id = $_REQUEST['acc_id'];
	$query="UPDATE acc_master SET acc_status='1' WHERE acc_master_id = '".$acc_id."'";
	$result = $db->query($query);
	$return_data  = array('status' => true);
	echo json_encode($return_data);
}
   
?>