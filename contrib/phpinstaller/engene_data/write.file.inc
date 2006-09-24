<?php
class write_file{
	
	function __construct(){
		$this->to = gpv('path');
	}
	
	function step_dir($action,&$step,$steptotal){
		$dirname = $action[2];
		$mkdir_path = $this->to.$dirname;
		if(trim($dirname)==''){$dirname = '/';}
		$str = str_pad('Adding directory: '.$dirname.' ' , PAD_LENGTH, ".");
		if(is_dir($mkdir_path)){
			$str.= '[<span style="color:#BB0;font-weight:bold;">exists</span>]';
		}else{
			if(file_exists($mkdir_path) || mkdir($mkdir_path)===false){
				$str.= '[<span style="color:red;">failed</span>]';
				$str.= '<div style="color:red;"><b> &gt;&gt; Failed: could not make directory '.$path.$dirname.' Exiting...</b></div>';
				updatepb((($i/$count)*100),$str);
				die($str);
			}else{
				$str.= '[<span style="color:green;">  ok  </span>]';
			}
		}
		$step+=$action[0];
		updatepb( ($step/$steptotal*100) ,$str);
	}
	
	function step_file($action,&$step,$steptotal){
		$file_open = ($this->to.($fname=$action[2]));
		$str = str_pad('Adding file: '.$fname.' ' , PAD_LENGTH, ".");
		$fo = fopen($file_open,'w');
		if($fo===false){
			$str.= '[<span style="color:red;">failed</span>]';
			$str.= '<div>Failed: could not create file. Exiting...</div>';
			updatepb((($step/$steptotal)*100),$str);
			die($str);
		}else if($action[3] && !fwrite($fo,$action[3])){
			$str.= '[<span style="color:red;">failed</span>]';
			$str.= '<div>Failed: could write to file. Exiting...</div>';
			updatepb((($step/$steptotal)*100),$str);
			die($str);
		}else{
			fclose($fo);
			$str.= '[<span style="color:green;">  ok  </span>]';
		}
		$step+=$action[0];
		updatepb( ($step/$steptotal*100) ,$str);
	}
	
	function path($path='/'){
		return "file://".$this->to.$path;
	}
	
	function exists($path='/'){
		return file_exists($this->to.$path);
		
	}
	
	function is_writable($path='/'){
		
		
	}
	
}

?>