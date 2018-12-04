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


function CSVtoDB($dir, $csv, $model){
	$filePath = $dir . "/" . $csv;
	$isHeader = true;

	$name = explode(".", $csv)[0];

	// read csv file line by line
	$isHeader = true;
	if (($handle = fopen($filePath, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {	    	
	    	// create new table using header of csv(first row)
	    	if ($isHeader){	    		
	    		$model->creatTable();
	    		$isHeader = false;
	    	}else{	    		
	    		$model->set($data);
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
		$modelName = str_replace("IPGOLD", '', explode(".",$filelist[$i])[0]);
		$model = null;
		// echo $modelName;exit();
		switch ($modelName) {
			case '201':
				$model = new IPGOLD201();
				break;
			case '202':
				$model = new IPGOLD202();
				break;
			case '203':
				$model = new IPGOLD203();
				break;
			case '204':
				$model = new IPGOLD204();
				break;			
			case '206':
				$model = new IPGOLD206();
				break;
			case '207':
				$model = new IPGOLD207();
				break;
			case '208':
				$model = new IPGOLD208();
				break;
			case '220':
				$model = new IPGOLD220();
				break;
			case '221':
				$model = new IPGOLD221();
				break;
			case '222':
				$model = new IPGOLD222();
				break;

			default:
				break;
		}
		if (isset($model)){
			CSVtoDB('./ipgold-offline', $filelist[$i], $model);
			echo $filelist[$i]. " Done!<br>";
		}
	}
}

main();