<?php
set_time_limit(100000);
require_once('Model.php');

// download zip file from url
function PullZip($url){
	$destination = "ipgold". uniqid(time(), true) .".zip";
	$fh = fopen($destination, 'w');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_FILE, $fh); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
	curl_exec($ch);
	curl_close($ch);
	fclose($fh);

	return $destination;
}

// Extract csv files from zip
function Unzip($file, $path){
	$zip = new ZipArchive;
	$res = $zip->open($file);
	if ($res === TRUE) {
	  $zip->extractTo($path);
	  $zip->close();
	  echo 'extract all!<br>';
	} else {
	  echo 'error!<br>';
	}
}


// get all file name list in directory
function getCSVfilelist($dir){
    return scandir($dir);
}


function CSVtoDB($dir, $csv){
	$filePath = $dir . "/" . $csv;
	$isHeader = true;

	$name = explode(".", $csv)[0];
	// create Model with file name
	$model = new Model($name);

	// read csv file line by line
	$isHeader = true;
	if (($handle = fopen($filePath, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
	    	$line = join(",", $data);

	    	// create new table using header of csv(first row)
	    	if ($isHeader){
	    		$model->set($line);
	    		$model->creatTable();
	    		$isHeader = false;
	    	}else{
	    		$model->set($line);
	    		$model->save();
	    	}	    	
	    }
	    fclose($handle);
	}
}

function main(){
	// pull zip file from server
	$zipfile = PullZip(ZIP_URL);
	// extract zip file 
	Unzip($zipfile,'./');
	// get all csv file names 
	$filelist = getCSVfilelist('./ipgold-offline');

	// import csv data to db
	for ($i = 2; $i < count($filelist); $i++){
		CSVtoDB('./ipgold-offline', $filelist[$i]);
		echo $filelist[$i]. " Done!<br>";		
	}
}


/*
function dump(){	
	$dumpfname = DB_NAME .".sql";
	$query = "SHOW CREATE database `".DB_NAME."`";
	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$res = $db->query($query);
	$file = fopen($dumpfname, "w") or die('Unable to write');
	while($r=$res->fetch_assoc()) {
    	fwrite($file, $r['Create Database']);
	}
	fclose($file);

	if(file_exists($dumpfname)){
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($dumpfname));;
		flush();
		// readfile($dumpfname);
		// exit;
	}
}*/





main();