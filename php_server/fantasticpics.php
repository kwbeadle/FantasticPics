<?php
$myServer = "http://www.kbeadle.com/fantasticpics.php";
$server_list = array(
  "master" => "http://www.kbeadle.com/fantasticpics.php",
  "node1" => "/fantasticpics.php",
  "node2" => "/fantasticpics.php",
  "node3" => "/fantasticpics.php"
);

function STOR($target_file) {
  $path_parts = pathinfo($target_file);
  $uploaddir = "./" . $path_parts["dirname"] . "/";
  if (!is_dir($uploaddir)) {
    mkdir($uploaddir, 0777, true);
  }
  $uploadfile = $uploaddir . $path_parts["basename"];
	if (move_uploaded_file($_FILES['file_contents']['tmp_name'], $uploadfile)) {
	    echo "File is valid, and was successfully uploaded.\n";
	} else {
	    echo "Upload failed!\n";
	}
}

function delete_file_cmd($src_file, $target_file, $target_url) {
  global $myServer;
  $file_name_with_full_path = realpath($src_file);  
  $post = array("SRC" => $myServer, "CMD" => "DELE", "FILE" => $target_file);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
}

function send_file_cmd($src_file, $target_file, $target_url) {
  global $myServer;  
  $file_name_with_full_path = realpath($src_file);
  $post = array("SRC" => $myServer, "CMD" => "STOR", "FILE" => $target_file, "file_contents" => "@" . $file_name_with_full_path);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
  curl_close($ch);
}

function proc_cmd_to_master() {
  global $CMD, $SRC, $FILE, $server_list, $myServer;

  // Logic for master server
  if ($SRC === "client") {
    
    // Command comming from client.
    if ($CMD === "DELE") {

      // Delete file.
      foreach ($server_list as $key => $val) {
        if ($myServer != $val) {
          delete_file_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "MKD") {

      // Make directory.
    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($myServer != $val) {
          send_file_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "SYNC") {

      // Get files list and sync files with other servers
    }
  } else {

    // Command coming from other server nodes.
    if ($CMD === "DELE") {

    } else if ($CMD === "MKD") {

    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($SRC != $val && $myServer != $val) {
          send_file_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "SYNC") {

    }
  }
}

function proc_cmd_to_node() {
  global $CMD, $SRC, $FILE, $server_list, $myServer;

  // Logic for other nodes
  if ($SRC === "client") {
    if ($CMD === "DELE") {

    } else if ($CMD === "MKD") {

    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);

      // Send file to master server.
      send_file_cmd($FILE, $FILE, $server_list["master"]);                
    } else if ($CMD === "SYNC") {

    }
  } else if ($SRC == $server_list["master"]) {
    if ($CMD === "DELE") {

    } else if ($CMD === "MKD") {

    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);             
    } else if ($CMD === "SYNC") {

    }
  }
}

#extract($_POST);
if (isset($_POST["CMD"]) && isset($_POST["SRC"])) {
  $CMD = $_POST["CMD"];
  $SRC = $_POST["SRC"];
} else {
  die("Error processing request");
}
$FILE = isset($_POST["FILE"]) ? $_POST["FILE"] : NULL;

if ($myServer === $server_list["master"]) {
  proc_cmd_to_master();
} else {
  proc_cmd_to_node();
}
?>

