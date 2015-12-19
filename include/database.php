<?php
class Database {
	private $conn;
	private $addr;
	private $user;
	private $pass;
	private $db;

	public function __construct($addr,$user,$pass,$db){
		$this->addr = $addr;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;

		$this->conn = new mysqli($addr,$user,$pass,$db);
		
		if($this->conn->connect_error !== null){
			throw new Exception($this->conn->connect_error);
		}
	}
	
	private function reconnect(){
		$this->conn = new mysqli($this->addr,$this->user,$this->pass,$this->db);
	}

	private function checkConn(){
		return $this->conn->ping();
	}

	public function query($query,$data = array()){
		if(!$this->checkConn()) $this->reconnect();
		
		foreach($data as $k=>$d){
			$data[$k] = $this->conn->real_escape_string($d);
		}
		
		$result = $this->conn->query(vsprintf($query,$data));
		
		if($result === false){
			throw new Exception($this->conn->error);
		}
		
		return $result;
	}
};