<?php
//include vendor
require '../vendor/autoload.php';
use \Firebase\JWT\JWT;

//include headers
header("Access-Control-Allow-Origin: *");
header("Content-type:application/json; charset:UTF-8");
header("Access-Control-Allow-Methods: GET");

ini_set('display_errors',1);
//include requered files
include_once("../config/database.php");
include_once("../classes/user_details.php");

$db = new Database();
$mysql = $db->connect();

$in_usr = new UserDetail($mysql);

if($_SERVER['REQUEST_METHOD'] === "GET"){

  $all_headers = getallheaders();
  $jwt = $all_headers['Authorization'];
  if(!empty($jwt)) {
    $sec_key = "jar369";
    try {
      $u_data = JWT::decode($jwt,$sec_key, array('HS512'));
      $u_id = $u_data->data->id;

      $data = $in_usr->get_all_data();

      if($data->num_rows > 0){
        $res['records'] = array();
        while($row = $data->fetch_assoc()){
          array_push($res['records'],array(
            'user_id' => $row['user_id'],
            'user_email' => $row['user_email'],
            'user_name' => $row['user_name'],
            'user_pass' => $row['user_pass'],
            'user_mobile' => $row['user_mobile']
          ));
        }
        http_response_code(200);
        echo json_encode(array(
          'status' => 1,
          'message' => 'success',
          'data' => $res['records']
        ));
      }
      else {
        http_response_code(200);
        echo json_encode(array(
          'status' => 0,
          'message' => 'no records found',
          'data' => []
        ));
      }
    }
    catch(Exception $ex){
      http_response_code(503);//503 service not available
      echo json_encode(array(
        "status" => 0,
        "message" => "token expired"
      ));
    }
  }
  else {
      http_response_code(503);//503 means service not available
      echo json_encode(array(
        "status" => 0,
        "message" => "please login to continue"
      ));
  }
}
else {
  http_response_code(503);
  echo json_encode(array(
    'status' => 0,
    'message' => 'access denied',
    'data' => []
  ));
}

?>
