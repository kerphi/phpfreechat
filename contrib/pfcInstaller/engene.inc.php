<?php
class phpInstaller{
	
	/** Applactation Name
	  */
	public $appName = '';
	
	/** Short applactation name, with no spaces
	  * for use in file names
	  */
	public $appId = '';
	
	/** Engene Path
	  * use $this->dataDir($path) to set
	  */
	protected $dataDir = '';
	
	/** Virtual File System Folders/Directories
	  */
	protected $vfsDir = array();
	
	/** Virtual File System Files
	  */
	protected $vfsFiles = array();
	
	/** Virtual File System Sizes
	  */
	protected $vfsSizes = array();
	
	/** Total size being packed
	  */
	protected $vfsSize = 0;
	
	/** installerPageData
	  */
	protected $pageData = '';
	
	/** installerPageData
	  */
	protected $pageID = 1;
	
	/** installer forms
	  */
	protected $pageHidden = array();
	
	/** Extra files to include in installer
	  * bring them up with installer.php?f=NAME
	  * add a file with the {@link phpInstaller::addMetaFile} method
	  */
	protected $fileData = '';
	
	/** Files to ignore
	  * if file contains any value in array
	  */
	public $ignore = array(
		'/installer.php',
		'/thephpinstallerdev/'
		);
	
	/**
	  */
	function addPath($path){
		echo "<div>Adding files...</div>";
		$this->_add_file($path);
	}
	
	/** is this an ignored file?
	  * @return bool true for ignored, false to include
	  */
	protected function ignored_file($fname){
		foreach($this->ignore as $value){
			$value = str_replace('\\','/',$value);
			$fname = str_replace('\\','/',$fname);
			if(strpos($fname,$value)!==false){
				return true;
			}
		}
		return false;
	}
	
	protected function _add_file($base,$cfile='',$output=true){
		if($output) echo '&nbsp;&nbsp;&nbsp;';
		$base = realpath($base);
		if($output) echo $base.$cfile;
		if($this->ignored_file("$cfile/$file")){
			if($output) echo " - ignored<br />";
			return false;
		}
		if(is_file($base.$cfile)){
			$this->vfsFiles[$cfile] = file_get_contents($base.$cfile);
			$this->vfsSizes[$cfile] = strlen($this->vfsFiles[$cfile])+100;
			$this->vfsSize += $this->vfsSizes[$cfile];
			if($output) echo " - file<br />";
		}else if(is_dir($base.$cfile) && $dh=opendir($base.$cfile)){
			$this->vfsDir[] = $cfile;
			$this->vfsSize += 2;
			if($output) echo " - directory<br />";
			while (($file = readdir($dh)) !== false) {
				if($file!='.' && $file!='..'){
					$this->_add_file($base,$cfile.'/'.$file);
				}
			}
			closedir($dh);
		}else{
			if($output) echo " - Not a valid path<br />";
		}
	}
	function _get_name_from_str($name,$length){
		$ppath_arr = explode(' ',$name);
		$curr = '';
		$ppath = '';
		foreach($ppath_arr as $value){
			$curr.= $value;
			if(strlen($curr)>$length && strlen($ppath)>0){
				break;
			}else if(strlen($curr)>$length && strlen($ppath)==0){
				$ppath = $curr;
				break;
			}
			$ppath = $curr;
		}
		return $this->valid_chars(strtolower($ppath));
	}
	/** Add a file to the installer.
	  * Access it like this: installer.php?file=NAME
	  */
	function addMetaFile($name,$filePath,$contentType){
		$name = $this->valid_chars($name);
		if(file_exists($filePath)){
			$data = addslashes(file_get_contents($filePath));
			$this->fileData.= "case '$name':";
			$this->fileData.= "header('Content-Type: text/html');";
			$this->fileData.= "echo '".$data."';//dont touch this. Edit the license.html file instead.";
			$this->fileData.= "break;\n";
			return true;
		}else{
			return false;
		}
	}
	function generate($output='installer.php'){
		$dataDir = $this->dataDir;
		echo "<div>Reading Data files...</div>";
		$dataDir = realpath($dataDir);
		if(file_exists($dataDir.'/installer_data.txt')){
			$data1 = file_get_contents($dataDir.'/installer_data.txt');
		}else{
			echo 'file /installer_data.txt does not exist in data directory!<br />';
			return false;
		}
		if(file_exists($dataDir.'/step_path.txt')){
			$step_path = file_get_contents($dataDir.'/step_path.txt');
		}else{
			die('step_path.txt file not found.<br />');
		}
		if(file_exists($dataDir.'/step_aboutto.txt')){
			$step_aboutto = file_get_contents($dataDir.'/step_aboutto.txt');
		}else{
			die('step_aboutto.txt file not found.<br />');
		}
		if(file_exists($dataDir.'/step_finished.txt')){
			$step_finished = file_get_contents($dataDir.'/step_finished.txt');
		}else{
			die('step_aboutto.txt file not found.<br />');
		}
		echo "<div>Adding Installer Data...</div>";
		$this->addPage('Install Path',$step_path,array('path'));
		$this->addPage('About to Install',$step_aboutto);
		$this->addInstallerPage();
		$this->addPage('Finished',$step_finished);
		echo "<div>Adding headers...</div>";
		$data .= '<'.'?php $data=explode("\n",file_get_contents(__FILE__),2);$data=gzuncompress(str_replace(array("/?","//"),array("?","/"),$data[1]));$data=unserialize($data);eval($data[0]);die; ?'.'>';
		$data .= "\n";
		echo "<div>Adding constants...</div>";
		$data1 = str_replace('PROGRAM',$this->appName,$data1);
		$ppath = $this->_get_name_from_str($this->appName,20);
		echo 'constant PPATH: '.$ppath.'<br />';
		$data1 = str_replace('PAGES',$this->pageData,$data1);
		$data1 = str_replace('PPATH',$ppath,$data1);
		$data1 = str_replace('METAFILES',$this->fileData,$data1);
		$data1 = str_replace('STEP1',addcslashes($step1,"'\\"),$data1);
		$data1 = str_replace('INSTALLERSTEP',$this->installer_page,$data1);
		echo "<div>Compressing...</div>";
		$data .= str_replace(array('/','?'),array('//','/?'),gzcompress(serialize(array($data1,array($this->vfsFiles,$this->vfsDir,$this->vfsSizes,$this->vfsSize))),9));
		echo "<div>Writing to $output...</div>";
		$fo = fopen($output,'w');
		if($fo===false){echo 'Write failed';return false;}
		fwrite($fo,$data);
		fclose($fo);
		echo "<div><b>DONE!</b></div>";
		return true;
		}
	/** strip invalid chars from a string
	  */
	function valid_chars($str,$valid=null){
		$valid_chars_deafult = array(
		'a','b','c','d','e','f','g','h','i','j','k','l','m',
		'n','o','p','q','r','s','t','u','v','w','x','y','z',
		'A','B','C','D','E','F','G','H','I','J','K','L','M',
		'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		'1','2','3','4','5','6','7','8','9','0','_'
		);		
		if(!is_array($valid)){$valid = $valid_chars_deafult;}
		$return = '';
		for($i=0;$i<strlen($str);$i++){
			if(in_array($str{$i},$valid)){
				$return .= $str{$i};
			}
		}
		return $return;
	}
	/** Send a message to the installer consle.
	  */
	function message($str){
		$str = htmlspecialchars($str);
		echo "<div>$str</div>";
		return true;
	}
	
	/** add a page to the installer
	  */
	function addPage($title,$content,$uses=array()){
		$title = addslashes($title);
		foreach($uses as $value){
			$this->pageHidden[] = $value;
		}
		$page = $this->pageID++;
		$this->pageData.= "\$pages[$page]['title'] = '$title';\n";
		$this->pageData.= 'if($step=='.$page.'){';
		$this->pageData.= $content;
		$this->pageData.= '}';
		return $page;
		
	}
	
	protected function addInstallerPage(){
		$fc = file_get_contents($this->dataDir.'/step_installer.txt');
		$this->installer_page = $this->addPage('Install',$fc);
		echo '<div>Page: '.$this->installer_page.'</div>';
	}
	
	function dataDir($path){
		$this->dataDir = realpath($path);
	}
	
}
?>