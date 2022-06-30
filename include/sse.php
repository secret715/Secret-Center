<?php
/*Server-Sent Events*/

class sse{
    private $max_execution_time;
    private $sleep;//控制睡眠多久再執行 (s)
    private $method;
    private $start_time;//起始時間，資料庫篩選資料判斷使用

    public function __construct($method,$sleep=1,$max_execution_time=0,$start_time=0){
        if($method===null){
            throw new Exception('method cannot be null');
        }
        $this->method=$method;
        $this->sleep=$sleep;
        $this->max_execution_time=$max_execution_time;
        $this->start_time=$start_time;
    }
    public function start(){
        while($this->method!=null){
            $return=call_user_func($this->method,$this->start_time);
            if($return!=null){
                echo $return;
                ob_flush();
                flush();
                $this->start_time=time();
            }
            sleep($this->sleep);
        }
    }

    public function stop(){
        $this->method=null;
    }
}