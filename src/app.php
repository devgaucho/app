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
	function asset($assetName,$print=false){
		// verifica se é css ou js
		$arr=explode('/',$assetName);
		$ext=$arr[0];
		// gera o nome do arquivo
		$filename=$this->root().'/'.$assetName;
		$extensoesPermitidas=[
			'css',
			'js'
		];	
		if(
			in_array($ext,$extensoesPermitidas) and
			file_exists($filename)
		){
			if($print){
				$this->ext2mime($ext);
				return print file_get_contents($filename);
			}
		// gera o hash
			$hash=md5_file($filename);
			$url=$this->url();
		// gera o href
			$href=$url['root'];
			$href.='/static/'.$hash.'/'.$assetName;
			return $href;
		}else{
			return null;
		}
	}	
	function ext2mime($ext){
		$mimes=[
			// diversos
			'txt'=>'text/plain; charset=utf-8',
			'htm'=>'text/html; charset=utf-8',
			'html'=>'text/html; charset=utf-8',
			'php'=>'text/html; charset=utf-8',
			'css'=>'text/css; charset=utf-8',
			'js'=>'application/javascript; charset=utf-8',
			'json'=>'application/json; charset=utf-8',
			'xml'=>'application/xml; charset=utf-8',
			'swf'=>'application/x-shockwave-flash',
			'flv'=>'video/x-flv',
			'mustache'=>'text/html; charset=utf-8',

         	// images
			'png'=>'image/png',
			'jpe'=>'image/jpeg',
			'jpeg'=>'image/jpeg',
			'jpg'=>'image/jpeg',
			'gif'=>'image/gif',
			'bmp'=>'image/bmp',
			'ico'=>'image/vnd.microsoft.icon',
			'tiff'=>'image/tiff',
			'tif'=>'image/tiff',
			'svg'=>'image/svg+xml',
			'svgz'=>'image/svg+xml',
			'webp'=>'image/webp',

        	// archives
			'zip'=>'application/zip',
			'rar'=>'application/x-rar-compressed',
			'exe'=>'application/x-msdownload',
			'msi'=>'application/x-msdownload',
			'cab'=>'application/vnd.ms-cab-compressed',
			'bin'=>'application/octet-stream',

        	// audio/video
			'mp4'=>'video/mp4',
			'mp3'=>'audio/mpeg',
			'qt'=>'video/quicktime',
			'mov'=>'video/quicktime',

        	// adobe
			'pdf'=>'application/pdf',
			'psd'=>'image/vnd.adobe.photoshop',
			'ai'=>'application/postscript',
			'eps'=>'application/postscript',
			'ps'=>'application/postscript',

        	// ms office
			'doc'=>'application/msword',
			'rtf'=>'application/rtf',
			'xls'=>'application/vnd.ms-excel',
			'ppt'=>'application/vnd.ms-powerpoint',
			'docx'=>'application/msword',
			'xlsx'=>'application/vnd.ms-excel',
			'pptx'=>'application/vnd.ms-powerpoint',

         	// open office
			'odt'=>'application/vnd.oasis.opendocument.text',
			'ods'=>'application/vnd.oasis.opendocument.spreadsheet',
		];
		if(isset($mimes[$ext])){
			header('Content-Type: '.$mimes[$ext]);
		}
	}	
	function fn($fn,...$arguments){
		return $this->__call($fn,$arguments);
	}
	function isAjax(){
		if(
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		){
			return true;
		}else{
			return false;
		}
	}
	function mustache($name,$params=[]){
		$viewsPath=$this->root().'/app';	
		$filename=$viewsPath.'/'.$name.'.html';
		if(file_exists($filename)){
			$optsFile=[
				'extension' => '.html'
			];
			$opts=[
				'entity_flags' => ENT_QUOTES,
				'loader' => new \Mustache_Loader_FilesystemLoader(
					$viewsPath,
					$optsFile
				),
			];
			$m = new \Mustache_Engine($opts);
			echo $m->render($name,$params);	
		}else{
			$msg='view <b>'.$filename.'</b> not found';
			die($msg);
		}
	}
	function method($raw = false) {
		$method = $_SERVER['REQUEST_METHOD'];
		if ($raw) {
			return $method;
		} else {
			if ($method == 'POST') {
				return 'POST';
			} else {
				return 'GET';
			}
		}
	}
	function root(){
		return realpath(__DIR__.'/../../../../');
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
	function showErrors($bool){
		if ($bool) {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
		} else {
			ini_set('display_errors', 0);
			ini_set('display_startup_errors', 0);
			error_reporting(0);
		}
	}
	function url(){
		$data=[
			'protocol'=>$_SERVER['REQUEST_SCHEME'],
			'host'=>$_SERVER['HTTP_HOST'],
			'uri'=>$_SERVER['REQUEST_URI']
		];
		$url=$data['protocol'].'://'.$data['host'].'/'.$data['uri'];
		$data['url']=$url;
		if($data['host']=='localhost'){
			$firstPath=explode('/',$data['uri'])[1];
			$root=$data['protocol'].'://'.$data['host'];
			$root.='/'.$firstPath;
		}else{
			$root=$data['protocol'].'://'.$data['host'];
		}
		$data['root']=$root;
		return $data;
	}
}