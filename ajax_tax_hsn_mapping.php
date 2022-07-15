<?php 
require_once("../lib/config.php");
require_once("../lib/constants.php");
require_once('../Classes/PHPExcel.php');
require_once('../Classes/PHPExcel/IOFactory.php');
$logged_user_id = my_session('user_id');
if(isset($_REQUEST['source']) && ($_REQUEST['source'] == 'app')){
$logged_user_id = $_REQUEST['user_id'];
}
$role_id =  my_session('role_id');
$action_type = $_REQUEST['action_type'];

if($action_type == 'LIST_ALL_TAX'){
  $query = "SELECT tax_id, tax_name, tax_percentage FROM tax_master  order by tax_name";
    $result = $db->query($query);

    while($data=mysqli_fetch_assoc($result)){
      $ret[] = $data;
    }
    $return_data  = array('status' => true, 'tax_list'=>$ret );
    echo json_encode($return_data);
}

elseif($action_type == 'LIST_ALL_HSN_SAC'){
  $query = "SELECT hsn_sac_id, hsn_sac_code FROM hsn_sac_master";
    $result = $db->query($query);

    while($data=mysqli_fetch_assoc($result)){
      $ret[] = $data;
    }
    $return_data  = array('status' => true, 'hsn_sac_list'=>$ret );
    echo json_encode($return_data);
}

elseif($action_type=="MAPPING_LISTING")
{
  $query = "SELECT th.tax_hsn_mapping_id, th.tax_id, tm.tax_name, th.hsn_sac_id, hs.hsn_sac_code, ifnull(date_format(th.eff_start_date,'%d-%m-%Y'),'') eff_start_date, th.is_active FROM tax_hsn_mapping th INNER JOIN tax_master tm ON tm.tax_id=th.tax_id INNER JOIN hsn_sac_master hs ON hs.hsn_sac_id= th.hsn_sac_id order by th.eff_start_date DESC";
  $result = $db->query($query);
  while($data=mysqli_fetch_assoc($result))
  {
  	$ret[] = $data;
  }
$return_data  = array('status' => true, 'mapping_list'=>$ret,'query'=>$query);
echo json_encode($return_data);

}

// elseif($action_type == "active"){
// $list_s_id_ai = $_REQUEST['list_s_id_ai'];

//    $query = "update uom_master set is_active=0 where uom_id=".$list_s_id_ai;	
//    $result = $db->query($query);	
//    $return_data = array('status' => true);
//    echo json_encode($return_data);
// }	
// elseif($action_type == "inactive"){
// 	$list_s_id_ai = $_REQUEST['list_s_id_ai'];

//    $query = "update uom_master set is_active=1 where uom_id=".$list_s_id_ai;	
//    $result = $db->query($query);	
//    $return_data = array('status' => true);
//    echo json_encode($return_data);
// }

// ifnull(date_format(eff_start_date,'%d-%m-%Y'),'')eff_start_datea

elseif ($action_type=="UPDATE_TAX_HSN") {
    $tax_hsn_mapping_id=$_REQUEST['tax_hsn_mapping_id'];
     $query = "SELECT tax_hsn_mapping_id, tax_id, hsn_sac_id, ifnull(date_format(eff_start_date,'%d-%m-%Y'),'')eff_start_datea  from tax_hsn_mapping WHERE tax_hsn_mapping_id='".$tax_hsn_mapping_id."'";
      $result = $db->query($query);
		 if($result)
		 {
		   $ret = mysqli_fetch_assoc($result);
		   $return_data  = array('status' => true,'tax_hsn_list'=>$ret);
		 }
		echo json_encode($return_data);

}

elseif ($action_type=="ADD_EDIT_UOM") 
{
    $tax_id =$_REQUEST['tax_id'];
    $hsn_id = $_REQUEST['hsn_id'];
    $eff_strat_date = $_REQUEST['eff_strat_date'];
    $eff_strat_date =date('Y-m-d', strtotime($eff_strat_date));
    $hidd_tax_hsn_mapping_id = $_REQUEST['hidd_tax_hsn_mapping_id'];

    $chk_code = "SELECT COUNT(tax_hsn_mapping_id) AS cnt FROM tax_hsn_mapping WHERE tax_id='".$tax_id."' AND hsn_sac_id='".$hsn_id."' AND tax_hsn_mapping_id !='".$hidd_tax_hsn_mapping_id."'";
    $chk_res = $db->query($chk_code);
    $chk_detail = mysqli_fetch_assoc($chk_res);
    $chk_detail['cnt'];
  if($chk_detail['cnt']>0)
  {
    $id = 0;
  }  
elseif($hidd_tax_hsn_mapping_id=="" || $hidd_tax_hsn_mapping_id==" "){

    $query =  "INSERT INTO tax_hsn_mapping(tax_id, hsn_sac_id, eff_start_date, created_by, created_ts) VALUES ('".$tax_id."','".$hsn_id."','".$eff_strat_date."','".$logged_user_id."',now())";
    $result = $db->query($query);
    if($result)
    {
        $id = 1;
    }
    else
    {
        $msg = "Try again !";
    }
}
else{

   $upd_query = "UPDATE tax_hsn_mapping SET tax_id='".$tax_id."',hsn_sac_id='".$hsn_id."',eff_start_date='".$eff_strat_date."',updated_by='".$logged_user_id."',updated_ts=now() WHERE tax_hsn_mapping_id='".$hidd_tax_hsn_mapping_id."'";
   $res = $db->query($upd_query);
   if($res)
   {
   	//$msg = "Updated sucessfully !";
   $id = 2;
   }
   else
   {
   	$msg = "Try again !";
   }
}
 $return_data  = array('status' => true,'msg'=>$chk_code,'id'=>$id);
 echo json_encode($return_data);

}  


?>

