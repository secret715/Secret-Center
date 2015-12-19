<?php
abstract class Channel {
	public $count;
	public $timeout = 15;
	protected $payload;
	
	public function __construct(){
		$this->count = intval(@$_GET['e']);
		$this->payload = isset($_GET['payload']) ? unserialize(base64_decode($_GET['payload'])) : null;
	}
	
	protected function flush(){
		@ob_flush();
		flush();
	}
	
	protected function timeoutResponse(){
		$data = array(
			'status' => 'CONTINUE',
			//'payload' => base64_encode(serialize($this->payload)),
			'data' => null
		);
		echo json_encode($data);
		$this->flush();
	}
	
	protected function response($result){
		$data = array(
			'status' => 'DATA',
			'payload' => base64_encode(serialize($this->payload)),
			'data' => $result
		);
		echo json_encode($data);
		$this->flush();
	}
	
	public function update(){
		return false;
	}
	
	public function start(){
		@set_time_limit(0);
		$start = time();
		
		while(($result = $this->update()) === false){
			if(time() - $start > $this->timeout){
				$this->timeoutResponse();
				die();
			}
			sleep(1);
		}
		
		header("Content-type: application/json");
		$this->response($result);
		die();
	}
}