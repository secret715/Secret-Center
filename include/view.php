<?php
class View {
	private $master_content;
	private $nav_content;
	private $scripts;
	private $title;
	private $part;
	private $tag_list;

	public function __construct($master,$title,$part,$nav){
		$this->load($master,$nav);
		$this->title = $title;
		$this->part = $part;
		ob_start();
	}

	private function load($master,$nav){
		ob_start();
		include($master);
		$this->master_content = ob_get_contents();
		ob_end_clean();

		ob_start();
		include($nav);
		$this->nav_content = ob_get_contents();
		ob_end_clean();
	}
	
	public function addMeta($name,$meta_content){
		$this->scripts .= sprintf('<meta name="%s" content="%s" />'.PHP_EOL,$name,$meta_content);
	}

	public function addScript($url,$type="text/javascript"){
		$this->scripts .= sprintf('<script type="%s" src="%s"></script>'.PHP_EOL,$type,$url);
	}

	public function addCSS($url,$type="text/css"){
		$this->scripts .= sprintf('<link type="%s" rel="stylesheet" href="%s" />'.PHP_EOL,$type,$url);
	}
	
	public function addBlock($tag,$data,$include=false){
		if($include){
			ob_start();
			include($data);
			ob_end_clean();
			$tag_list['{'.$tag.'}']= ob_get_contents();
		}else{
			$tag_list['{'.$tag.'}']=$data;
		}
	}

	public function render(){
		$content = ob_get_contents();
		ob_end_clean();
		
		$tag_list['{title}']=$this->title;
		$tag_list['{part}']=$this->part;
		$tag_list['{scripts}']=$this->scripts;
		$tag_list['{nav}']=$this->nav_content;
		$tag_list['{content}']=$content;

		echo strtr($this->master_content,$tag_list);
		@ob_flush();
		flush();
	}
};