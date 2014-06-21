<?php
  // get full path
  define("OCC_ROOT", realpath(dirname(__FILE__)));
  
  require  (OCC_ROOT.'/config/db.php');

  /* this was written using http://code.tutsplus.com/tutorials/why-you-should-be-using-phps-pdo-for-database-access--net-12059 */
  function connectToDb($host,$database,$user,$pass){
    try {
      $db = new PDO("mysql:host=$host;dbname=$database", $user, $pass, array(PDO::ATTR_PERSISTENT => true)); // open db connection and cache it
//      print "databse opened\n";
      return $db;
    } catch (PDOException $pdoex) {
      die($pdoex->getMessage());
    }
  }

  /* assures the existence of the config table */
  function checkConfigTable($db){
    $results=$db->query("SHOW TABLES LIKE 'config'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE config (keyname VARCHAR(100) PRIMARY KEY, value TEXT NOT NULL);';      
      $db->exec($sql);
      $sql = 'INSERT INTO config (keyname,value) VALUES ("dbversion","1")';
      $db->exec($sql);      
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the urls table */
  function checkUrlsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'urls'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE urls (uid INT PRIMARY KEY AUTO_INCREMENT, url TEXT NOT NULL);';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }

  /* assures the existence of the tags table */
  function checkTagsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'tags'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE tags (tid INT PRIMARY KEY AUTO_INCREMENT, keyword VARCHAR(100) NOT NULL);';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the sessions table */
  function checkSessionsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'sessions'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE sessions (sid INT PRIMARY KEY AUTO_INCREMENT, aid INT NOT NULL REFERENCES appointment(aid), description TEXT NOT NULL, start DATETIME NOT NULL, end DATETIME);';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the appointments table */
  function checkAppointmentsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'appointments'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE appointments (aid INT PRIMARY KEY AUTO_INCREMENT, title TEXT NOT NULL,description TEXT, start DATETIME NOT NULL, end DATETIME, location TEXT, coords TEXT);';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the appointment_urls table */
  function checkAppointmentUrlsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'appointment_urls'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE appointment_urls (aid INT NOT NULL REFERENCES appointments(aid),uid INT NOT NULL REFERENCES urls(uid), description TEXT, PRIMARY KEY (aid,uid));';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the appointment_sessions table */
  function checkAppointmentSessionsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'appointment_sessions'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE appointment_sessions (aid INT NOT NULL REFERENCES appointments(aid),sid INT NOT NULL REFERENCES sessions(sid), PRIMARY KEY (aid,sid));';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }
  
  /* assures the existence of the appointment_tags table */
  function checkAppointmentTagsTable($db){
    $results=$db->query("SHOW TABLES LIKE 'appointment_tags'");
    if (!$results){
      die(print_r($dbh->errorInfo(), TRUE));
    }
    if ($results->rowCount()<1){
//      echo "table doesn't exist\n";
      $sql = 'CREATE TABLE appointment_tags (aid INT NOT NULL REFERENCES appointments(aid),tid INT NOT NULL REFERENCES tags(tid), PRIMARY KEY (aid,tid));';
      $db->exec($sql);
//    } else {
//      echo "table exists\n";
    }
  }

  /* assures the existence of all required database tables */
  function checkTables($db){
    try {
      checkConfigTable($db);
      checkUrlsTable($db);
      checkTagsTable($db);
      checkAppointmentsTable($db);
      checkSessionsTable($db);
      checkAppointmentUrlsTable($db);      
      checkAppointmentTagsTable($db);      
    } catch (PDOException $pdoex){
      echo $pdoex->getMessage();
    }
  }

  function occ_autoload($class_name) {
    $path = OCC_ROOT . "/include/class." . $class_name . ".php";
//    echo "occ_autoload called for $class_name\n";
    if (file_exists($path)) {
      require_once($path);
    } else {
      return false;
    }
  }

  function loc($text){
    global $locale;
    if (isset($locale) && array_key_exists($text,$locale)){
      return $locale[$text];
    }
    return $text;
  }

  function parseDate($array){
    if (!isset($array['year'])){
      return false;
    }
    if (!isset($array['month'])){
      return false;
    }
    if (!isset($array['day'])){
      return false;
    }
    $d_string=$array['year'].'-'.$array['month'].'-'.$array['day'];
    return strtotime($d_string);
  }
  
  function parseTime($array){
    $secs=0;

    if (isset($array['hour'])){
      $hour=(int) $array['hour'];
      $secs+=3600 * $hour;
    }
    if (isset($array['minute'])){
      $min=(int) $array['minute'];
      $secs+=60 * $min;
    }
    return $secs;
  }

  function warn($message){
  	global $warnings;
    $warnings.='<p>'.loc($message).'</p>'.PHP_EOL;
  }
  
  function parseAppointmentData($data){
  	global $db_time_format;
  	if (empty($data['title'])){
  		warn('no title given');
  		return false;
  	}
  	$start=parseDate($data['start']);
  	if (!$start){
  		warn('invalid start date');
  		return false;
  	}
  	$end=parseDate($data['end']);
  	if (!$end){
  		warn('invalid end date');
  		return false;
  	}
  	$start+=parseTime($data['start']);
  	$end+=parseTime($data['end']);
  	if ($end<$start){
  		$end=$start;
  	}
  	$start=date($db_time_format,$start);
  	$end=date($db_time_format,$end);
  	$app=appointment::create($data['title'],$data['description'],$start,$end,$data['location'],$data['coordinates'],false);
  	if (isset($data['id'])){
  		$app->id=$data['id'];
  	}
  	return $app;
  }
  
  function parseSessionData($data){
  	global $db_time_format;
  	if (empty($data['description'])){
  		warn('no title given');
  		return false;
  	}
  	$start=parseDate($data['start']);
  	if (!$start){
  		warn('invalid start date');
  		return false;
  	}
  	$end=parseDate($data['end']);
  	if (!$end){
  		warn('invalid end date');
  		return false;
  	}
  	$start+=parseTime($data['start']);
  	$end+=parseTime($data['end']);
  	if ($end<$start){
  		$end=$start;
  	}
  	$start=date($db_time_format,$start);
  	$end=date($db_time_format,$end);
  	$session=session::create($data['aid'],$data['description'],$start,$end);
  	return $session;
  }

  $warnings = "";
  $db_time_format='Y-m-d H:i:0';
  
  spl_autoload_register('occ_autoload');
  
  $db = connectToDb($host,$database,$user,$pass);

  checkTables($db);

  require OCC_ROOT."/locale/de.php";
?>
