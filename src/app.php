<?php
namespace gaucho;
class app{
	var $cache;
	var $root;	
	function __call($fn,$arguments) {
		$name=str_replace('_','/',$fn);
		$arr=explode('/',$name);
		$fn=end($arr);
		$file=$name .'.php';
		$filename=$this->root.'/'.$file;
		if(
			!isset($this->cache[$name]) and
			file_exists($filename)
		){
			$this->cache[$name]=require $filename;
		}elseif(!isset($this->cache[$name])){
			die($filename.' not found');
		}
		return call_user_func_array(
			$this->cache[$name],
			$arguments
		);
	}
	function __construct($root){
		$this->root=$root;
	}
}