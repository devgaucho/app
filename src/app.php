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
	function fn($name,...$arguments){
		return $this->__call($fn,$arguments);
	}
	function method($raw = false) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($raw) {
			return strtolower($method);
		} else {
			if ($method == 'POST') {
				return 'post';
			} else {
				return 'get';
			}
		}
	}	
	function segment($segment=null){
		// 1) pega os dados do header
		$host=$_SERVER['HTTP_HOST'];
		$uri=$_SERVER["REQUEST_URI"];

		// 2) pega os diretórios
		$uri=explode('?',$uri)[0];

		// 3) transforma os diretórios em array
		if($uri=='/'){
			$arr[1]='/';
		}else{
			$arr=explode('/',$uri);
			$arr=array_filter($arr);	
			$arr=array_values($arr);
		}

		// 4) remove o primeiro diretório no localhost
		if($host=='localhost'){
			unset($arr[0]);
		}
		if(count($arr)=='0'){
			$arr[]='/';
		}	

		// 5) normaliza o array de saída
		$i=1;
		$out=null;
		foreach ($arr as $key => $value) {
			$out[$i]=$value;
			$i++;
		}
		$arr=$out;

		// 6) retorna o array ou o diretório específicado
		if(is_null($segment)){
			return $arr;
		}elseif(isset($arr[$segment])){
			return $arr[$segment];
		}else{
			return false;
		}
	}	
}