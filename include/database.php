<?php
class Database{
	private $conn;
	private $db;
	private $user;
	private $pass;
	private $host;
	

	public function __construct($db,$user,$pass,$host){
		$this->db = $db;
		$this->user = $user;
		$this->pass = $pass;
		$this->host = $host;
		
		$this->connect();
	}
	
	private function connect($persistant = false){
		$db = $this->db;
		$user = $this->user;
		$pass = $this->pass;
		$host = $this->host;
		
        $this->conn = $persistant ? new mysqli('p:'.$db,$user,$pass,$host) : new mysqli($db,$user,$pass,$host);
        $this->conn->set_charset('utf8mb4');
        
		if($this->conn->connect_error !== null){
			throw new Exception($this->conn->connect_error);
		}else{
			return true;
		}
    }
	
	private function checkConn(){
		return $this->conn->ping();
	}

	public function query($query,$data = array()){
		if(!$this->checkConn()) $this->connect();
		
		$data = $this->escapeString($data);
		$this->result = $this->conn->query(vsprintf($query,$data));
		
		if($this->result === false){
			throw new Exception($this->conn->error);
		}elseif (isset($this->result->num_rows)&&$this->result->num_rows > 0) {
			return $this->fetchAssocArray();
		}else{
			return $this->result;
		}
	}
	
	public function insert($table,$data = array(),$other = false){
		$data=$this->escapeString($data);
        $query = "INSERT INTO `{$table}` SET ";
		$first=true;
        foreach ($data as $_k => $_v) {
			if(!$first){
				$query.=' , ';
			}
            $query .= "`{$_k}` = '{$_v}'";
			$first=false;
		}
		if($other){
			 $query .= " {$other}"; 
		}

        return $this->query($query);
	}
	
	
	public function delete($table,$data = array(), $limit = false, $other = false){
		$data=$this->escapeString($data);
        $query = "DELETE FROM `{$table}` WHERE ";
		$first=true;
        foreach ($data as $_k => $_v) {
			if(!$first){
				$query.=' AND ';
			}
            $query .= "`{$_k}` = '{$_v}'";
			$first=false;
		}
		if($other){
			 $query .= " {$other}"; 
		}
		if($limit){
			 $query .= " LIMIT {$limit}";
		}
        return $this->query($query);
	}
	
	public function select($table,$data = array(), $order_by = false, $sort = true, $limit = false, $other = false){
		$data=$this->escapeString($data);
        $query = "SELECT * FROM `{$table}` ";
		$first=true;
        foreach ($data as $_k => $_v) {
			if(!$first){
				$query.=' AND ';
			}else{
				$query.=' WHERE ';
			}
            $query .= "`{$_k}` = '{$_v}'";
			$first=false;
		}
		if($other){
			 $query .= " {$other}"; 
		}
		if($order_by){
			 $query .= " ORDER BY `{$order_by}`"; 
			if($sort){
				 $query .= " ASC"; 
			}else{
				 $query .= " DESC";
			}
		}
		if($limit){
			 $query .= " LIMIT {$limit}";
		}
		$return = $this->query($query);
		
        return $return;
	}
	
	
	public function update($table,$data = array(),$condition = array(), $other = false){
		$data=$this->escapeString($data);
		$condition=$this->escapeString($condition);
        $query = "UPDATE `{$table}` SET ";
		$first=true;
        foreach ($data as $_k => $_v) {
			if(!$first){
				$query.=' , ';
			}
            $query .= "`{$_k}` = '{$_v}'";
			$first=false;
		}
		$query .= " WHERE ";
		$first=true;
        foreach ($condition as $_k => $_v) {
			if(!$first){
				$query.=' AND ';
			}
            $query .= "`{$_k}` = '{$_v}'";
			$first=false;
		}
		if($other){
			 $query .= " {$other}"; 
		}
        return $this->query($query);
	}
	
	public function escapeString($data){
		if(is_array($data)){
			foreach($data as $k=>$d){
				$data[$k] = $this->conn->real_escape_string($d);
			}
		}else{
			$data = $this->conn->real_escape_string($data);
		}
		return $data;
	}
	
	
	public function fetchAssocArray(){
        $this->fetchAssocArray = array();
        while ($data = $this->result->fetch_assoc()) {
            $this->fetchAssocArray[] = $data;
        }
        return $this->fetchAssocArray;
    }
	
	public function numRows(){
		return $this->result->num_rows;
	}
	public function lastInsertID(){
		return $this->conn->insert_id;
	}
	public function beginTransaction() {
		return $this->conn->begin_transaction();
	}
	public function commit($flags = 0 , $name =null) {
		return $this->conn->commit($flags,$name);
	}
	public function rollback($flags = 0 , $name =null) {
		return $this->conn->rollback($flags,$name);
	}
	
};