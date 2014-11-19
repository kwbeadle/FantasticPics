<?php
$myServer = "http://www.kbeadle.com/fantasticpics.php";
$server_list = array(
  "master" => "http://www.kbeadle.com/fantasticpics.php",
  "node1" => "http://php-sytes.rhcloud.com/fantasticpics.php",
  "node2" => "http://php2-sytes.rhcloud.com/fantasticpics.php",
  "node3" => "http://php3-sytes.rhcloud.com/fantasticpics.php"
);

function STOR() {
  #$uploaddir = realpath('./uploads') . '/';  
  $uploaddir = "./uploads/";
  if (!is_dir($uploaddir)) {
    mkdir($uploaddir, 0777, true);
  }
  $uploadfile = $uploaddir . basename($_FILES['file_contents']['name']);
	if (move_uploaded_file($_FILES['file_contents']['tmp_name'], $uploadfile)) {
	    echo "File is valid, and was successfully uploaded.\n";
	} else {
	    echo "Upload failed!\n";
	}
}

function send_file($file_name, $target_url) {
  $file_name_with_full_path = realpath($file_name);
  $post = array("SRC" => $myServer, "CMD" => "STOR", 'file_contents' => '@' .$file_name_with_full_path);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close ($ch);
}

#extract($_POST);
$CMD = $_POST["CMD"];
$SRC = $_POST["SRC"];

if ($myServer === $server_list["master"]) {

  // Logic for master server
  if ($SRC === "client") {
    
    // Command comming from client.
    if ($CMD === "DELE") {

      // Delete file.
    } else if ($CMD === "MKD") {

      // Make directory.
    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR();

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($myServer != $val) {
          send_file($_FILES["file_contents"]["name"], $val);          
        }
      }
    }
  } else {

    // Command coming from other server nodes.
    if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR();

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($SRC != $val) {
          send_file($_FILES["file_contents"]["name"], $val);          
        }
      } 
    }
  }
} else {

  // Logic for other nodes
  if ($SRC === "client") {
    if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR();

      // Send file to master server.
      send_file($_FILES["file_contents"]["name"], $server_list["master"]);                
    }
  } else if ($SRC == $server_list["master"]) {
    if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR();             
    }
  }
}
?>

