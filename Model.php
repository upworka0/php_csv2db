<?php
set_time_limit(100000);
/**
 * Model : Dynamic model for table
 * Created by Johan	 
 */
require_once ('config.php');

class Model
{
	/**
	 * constructor
	 * @param :  name(string) 
	 */
	public function __construct($name)
	{
		$this->Name = $name;  // table name
		$this->Text = "";     // line of csv
		$this->Dbcon = new mysqli(DB_HOST, DB_USER,DB_PASS,DB_NAME); // db instance
		if(mysqli_connect_error()) {
			echo "My sql connection Error!";
			exit;
		}
		$this->fields = array();  // schema
	}

	/**
	 * func Name: set
	 * @param 	: text(string)
	 * @return 	: none
	 */
	public function set($text){
		$this->Text = $this->format($text);
	}

	/**
	 * func name: format
	 * @param 	: text(string)
	 * @return 	: string
	 */
	function format($text){
		return str_replace("'", "''", str_replace('\n', '', str_replace('"', '', $text)));
	}

	/**
	 * func Name: runQuery
	 * @param 	: query(string)
	 * @return 	: none
	 */

	public function runQuery($query){
		$result = $this->Dbcon->query($query);
		if (!$result){
			echo $query."<br>";
			echo "Query Error!"."<br>";
		}
	}
	
	/**
	 * func Name: checkTableExists
	 * @param 	: tableName(string)
	 * @return 	: boolean
	 */	
	public function checkTableExists(){
		$query  = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '{$this->Name}'";
		$result = $this->Dbcon->query($query);
		$count  = $result->num_rows;
		while($r=$result->fetch_assoc()) {
		    if ($r['COUNT(*)'] == 1)
		    	return true;
		   	else
		   		return false;
		}
	}

	/**
	 * func Name: creatTable
	 * @param 	: none
	 * @return 	: none
	 */	
	public function creatTable(){				
		$names = explode(",", $this->Text);
		$this->fields = array('TM_NUMBER');

		$query = "CREATE TABLE " . $this->Name . " (`" . $names[0] . "` int(10) NOT NULL AUTO_INCREMENT,";
		for ($i = 1; $i < count($names) ; $i++){
			if ($names[$i] !='') {
				$query .= "`" . $names[$i] . "` text, ";
				array_push($this->fields, $names[$i]);
			}
		}
		
		$query .= "PRIMARY KEY (`".$names[0] . "`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";			
		if (!$this->checkTableExists($this->Name)){
			$this->runQuery($query);
		}
	}

	/**
	 * func Name: save
	 * @param 	: none
	 * @return 	: none
	 */	
	public function save(){

		$values = explode(",", $this->Text);

		// check record is already existed or not
		$query = "SELECT * from ". $this->Name . " WHERE `TM_NUMBER`= " . $values[0];
		$res = $this->Dbcon->query($query);
		
		if ($res->num_rows > 0){
			// true, update record
			$query = "UPDATE `" . $this->Name . "` SET ";
			for ($i = 1 ; $i < count($this->fields) ; $i++){
				$query .= "`" . $this->fields[$i] . "` = '" . $values[$i] . "',";
			}
			$query = substr($query, 0, strlen($query)-1) . " where `TM_NUMBER`=" . $values[0];
			// $this->runQuery($query);
		}else{
			// false, insert new record
			$query = "INSERT into `" . $this->Name . "` (" . join(",", $this->fields) . ") VALUES ( " . $values[0] .",";
			for ($i = 1 ; $i < count($this->fields) ; $i++){
				$query .= "'" . $values[$i] . "',";
			}
			$query = substr($query, 0, strlen($query)-1) . ")";
			$this->runQuery($query);
		}		
	}
}