<?php
	
class View {
	private $master_content;
	private $nav_content;
	private $scripts;
	private $title;
	private $part;
	private $non_base;

	public function __construct($master,$nav,$title,$part,$non_base = false){
		$this->load($master,$nav);
		$this->title = $title;
		$this->part = $part;
		$this->non_base = $non_base;
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

	public function addScript($url,$type="text/javascript"){
		$this->scripts .= sprintf('<script type="%s" src="%s"></script>'.PHP_EOL,$type,$url);
	}

	public function addCSS($url,$type="text/css"){
		$this->scripts .= sprintf('<link type="%s" rel="stylesheet" href="%s" />'.PHP_EOL,$type,$url);
	}

	public function render(){
		$content = ob_get_contents();
		ob_end_clean();
		
		echo strtr($this->master_content,array(
			'{title}' => $this->title,
			'{part}' => $this->part,
			'{style}' => $this->non_base ? '../style.css' : './style.css',
			'{scripts}' => $this->scripts,
			'{nav}' => $this->nav_content,
			'{content}' => $content
		));
		@ob_flush();
		flush();
	}
};