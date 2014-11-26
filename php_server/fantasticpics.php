<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

$myServer = "http://www.kbeadle.com/fp/fantasticpics.php";
// May store server list to MySQL database later
$server_list = array(
  "master" => "http://www.kbeadle.com/fp/fantasticpics.php",
  "node1" => "http://rajat-bansal.com/fantasticpics.php",
  "node2" => "http://vybbhav9.com/fantasticpics.php",
  "node3" => "http://gangania19.com/fantasticpics.php"
);
$master_db = array(
  "servername" => "",
  "username" => "",
  "password" => ""
);

function DELE($target_file) {
  $file_name_with_full_path = realpath($target_file);
  unlink($file_name_with_full_path); 
}

function NLST($target_dir) {
  $files = scandir($dir); 
  $list = array();
  foreach ($files as $file) {
    if (is_file($dir.$file)) {
      $list[] = $dir.$file;
    }
  }
  return $list;
}

function RMD($target_dir) {
  $it = new RecursiveDirectoryIterator($target_dir, RecursiveDirectoryIterator::SKIP_DOTS);
  $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
  foreach($files as $file) {
    if ($file->getFilename() === '.' || $file->getFilename() === '..') {
        continue;
    }
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
  }
  rmdir($target_dir);
}

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

function SYNC() {
  global $myServer, $server_list, $master_db;

  // Create mysql connection
  $conn = mysql_connect($master_db["servername"], $master_db["username"], $master_db["password"]);
  if (!$conn) {
     echo "Failed to connect to MySQL: " . mysql_error();
  }
  
  // Check if db exists
  $sql = "CREATE DATABASE IF NOT EXISTS fpdb COLLATE=utf8_unicode_ci";
  $result = mysql_query($sql);    
  if ($result === TRUE) {
    echo "Database created successfully";
  } else {
    echo "Error creating database: " . mysql_error();
  }

  // Setup files table
  $sql = "CREATE TABLE IF NOT EXISTS `fpdb`.`files` (`file` varchar(255)) ENGINE=MyISAM  DEFAULT COLLATE=utf8_unicode_ci";
  $result = mysql_query($sql);    
  if ($result === TRUE) {
    echo "Table created successfully";
  } else {
    echo "Error creating table: " . mysql_error();
  }

  // Clear table
  $sql = "DELETE FROM `fpdb`.`files`";
  $result = mysql_query($sql);    
  if ($result === TRUE) {
    echo "Table cleared successfully";
  } else {
    echo "Error clearing table: " . mysql_error();
  }

  // Make file list 
  $file_list = "";
  $di = new RecursiveDirectoryIterator("./",RecursiveDirectoryIterator::SKIP_DOTS);
  $it = new RecursiveIteratorIterator($di);
  foreach ($it as $file) {
    $path_parts = pathinfo($file);
    if ($path_parts["extension"] != "php") {
      $file_list .= "('" . $file . "'),";
    }
  }

  // Remove trailing comma
  $file_list = substr($file_list, 0, -1);

  // Insert file list into table
  $sql = "INSERT INTO `fpdb`.`files` (`file`) VALUES " . $file_list;
  $result = mysql_query($sql);    
  if ($result === TRUE) {
    echo "File list inserted into table successfully";
  } else {
    echo "Error inserting file list into table: " . mysql_error();
  }

  // Close mysql connection
  mysql_close($conn);


  // Send sync cmd to other servers.
  foreach ($server_list as $key => $val) {
    if ($myServer != $val) {
      send_sync_cmd($file_list, $val);          
    }
  }
}

function send_delete_cmd($src_file, $target_file, $target_url) {
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

function send_stor_cmd($src_file, $target_file, $target_url) {
  global $myServer;
  $file_name_with_full_path = realpath($src_file);
  $post = array("SRC" =>$myServer, "CMD" => "STOR", "FILE" => $target_file, "file_contents" => "@" . $file_name_with_full_path);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
  curl_close($ch);
  echo $result;
}

function send_sync_cmd($target_file, $target_url) {
  global $myServer;
  $post = array("SRC" => $myServer, "CMD" => "SYNC", "FILE" => $target_file);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);
  echo $result;
}

function send_retr_cmd($target_file, $target_url) {
  global $myServer;
  $post = array("SRC" => $myServer, "CMD" => "RETR", "FILE" => $target_file);  
  $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);
  echo $result;
}

function proc_cmd_to_master() {
  global $CMD, $SRC, $FILE, $server_list, $myServer, $master_db;

  // Logic for master server
  if ($SRC === "client") {
    
    // Command comming from client.
    if ($CMD === "DELE") {

      // Delete file.
      foreach ($server_list as $key => $val) {
        if ($myServer != $val) {
          send_delete_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "MKD") {

      // Make directory.
    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);

      // Add to database.
      $conn = mysql_connect($master_db["servername"], $master_db["username"], $master_db["password"]);
      if (!$conn) {
        echo "Failed to connect to MySQL: " . mysql_error();
      } 

      // Insert file name into table
      $sql = "INSERT INTO `fpdb`.`files` (`file`) VALUES " . "('".$FILE."')";
      $result = mysql_query($sql);    
      if ($result === TRUE) {
        echo "File name inserted into table successfully";
      } else {
        echo "Error inserting file name into table: " . mysql_error();
      }

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($myServer != $val) {
          send_stor_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "SYNC") {

      // Get files list and sync files with other servers
      SYNC();
    }
  } else {

    // Command coming from other server nodes.
    if ($CMD === "DELE") {

    } else if ($CMD === "MKD") {

    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);

      // Add to database.
      $conn = mysql_connect($master_db["servername"], $master_db["username"], $master_db["password"]);
      if (!$conn) {
        echo "Failed to connect to MySQL: " . mysql_error();
      } 

      // Insert file name into table
      $sql = "INSERT INTO `fpdb`.`files` (`file`) VALUES " . "('".$FILE."')";
      $result = mysql_query($sql);
      if ($result === TRUE) {
        echo "File name inserted into table successfully";
      } else {
        echo "Error inserting file name into table: " . mysql_error();
      }

      // Send file to other servers.
      foreach ($server_list as $key => $val) {
        if ($SRC != $val && $myServer != $val) {
          send_stor_cmd($FILE, $FILE, $val);          
        }
      }
    } else if ($CMD === "RETR") {

      // Process file request from node
      // Remove quotes and forward slash from string
      $replace_str = array('"', "'", ",", "\\"); 
      $FILE = str_replace($replace_str, "", $FILE); 
      send_stor_cmd($FILE, $FILE, $SRC);
    } else if ($CMD === "SYNC") {
      SYNC();
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
      send_stor_cmd($FILE, $FILE, $server_list["master"]);                
    } else if ($CMD === "SYNC") {
      send_sync_cmd("", $server_list["master"]);
    }
  } else if ($SRC == $server_list["master"]) {
    if ($CMD === "DELE") {

    } else if ($CMD === "MKD") {

    } else if ($CMD === "STOR") {

      // Accept data and store data as a file at the server site.
      STOR($FILE);            
    } else if ($CMD === "SYNC") {

      // Regular expression to extract file list
      $files = array();
      preg_match_all("/\(([^)]+)\)/", $FILE, $files);

      // Make list of current files
      $my_files = array();      
      $di = new RecursiveDirectoryIterator("./",RecursiveDirectoryIterator::SKIP_DOTS);
      $it = new RecursiveIteratorIterator($di);
      foreach ($it as $file) {
        $path_parts = pathinfo($file);
        if ($path_parts["extension"] != "php") {
          $my_files[] = "'" . (string)$file . "'";
        }
      }

      // Process any missing files by sending file retrieve command to master server   
      foreach ($files[1] as $file) {
        if (!in_array($file, $my_files)) {
          send_retr_cmd($file, $server_list["master"]);
        }
      }
    }
  }
}

// Add get methods 
//$CMD = isset($_GET["CMD"]) ? $_GET["CMD"] : NULL;

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

