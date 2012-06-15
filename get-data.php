<?php

$numOfFiles = 0;
$parameter = "Throughput";
$paramIdx = -1;
$average = "false";
$minObservation = -1;
$perFileObservation = 0;
$folder = "files";

$sum = array();

if (isset($_REQUEST['param'])) {
	$parameter = $_REQUEST['param'];
}

if (isset($_REQUEST['avg'])) {
	$average = $_REQUEST['avg'];
}

if (isset($_REQUEST['folder'])) {
	$folder = $_REQUEST['folder'];
}

if ($dir = opendir($folder)) {
	while (false !== ($filename = readdir($dir))) {
	   if ($filename == "." || $filename == "..")
	   continue;
	   //echo "<h1>$filename</h1><hr/>";
	   $handle = fopen($folder."/".$filename, "r");
	
	   $line = fgets($handle);
	   $newLine = str_replace(' ', '', trim($line));
	   $array = split(",", $newLine);
	   foreach ($array as $key => $value) {
	      echo $value."..";
	      if ($value == $parameter) {
	         $paramIdx = $key;
	         break;
	      }
	   }
	   if ($paramIdx == -1) return;
	
	   while(!feof($handle)) {
	      $line = fgets($handle);
	      $newLine = str_replace(' ', '', trim($line));
	      $array = split(",", $newLine);
	      
         if ($array[$paramIdx] == "") {
            continue;
         }
         
	      $sum[$array[0]] += $array[$paramIdx];
			if ($array[0] !== "") {
			   $perFileObservation = $array[0];
         }
	   }
	   if ($minObservation == -1) {
	      $minObservation = $perFileObservation;
      } else if ($minObservation > $perFileObservation) {
         $minObservation = $perFileObservation;
      }
	
	   $numOfFiles++;
	   fclose($handle);
	}
		
	closedir($dir);
	
	if ($average == "true") {
	   foreach ($sum as $key => $value) {
	   if ($key >= $minObservation) break;
	      $value /= $numOfFiles;
	      echo "$key|$value\n";
	   }
	} else {
	   foreach ($sum as $key => $value) {
	   if ($key >= $minObservation) break;
	      echo "$key|$value\n";
	   }
	}
}
?>
