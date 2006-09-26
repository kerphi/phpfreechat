<?php
/** Contains the class to generate a self-contained PHP installer
  * @todo also generate tarballs, ZIP files alongside the installer
  * @package webinstall
  */

/** Generator Class
  * @example 
  * @package webinstall
  */
class phpInstaller{
	
	/** Applactation Name
	  * @var string
	  */
	public $appName = '';
	
	/** Optional Applactation Version
	  * @var string
	  */
	public $appVersion;
	
	/** Optional Applactation Description
	  * Displayed on first page
	  * @var string
	  */
	public $appDescription;
	
	/** Short applactation name, with no spaces
	  * for use in file names
	  * @var string
	  */
	public $appId = '';
	
	/** Engene Path
	  * use $this->dataDir($path) to set
	  * @var string
	  */
	protected $dataDir = '';
	
	/** File names used
	  * @var array array('name'=>'filename')
	  */
	public $files = array(
		'installerdata'=>'installer_data.inc',
		'installpath'=>'step_path.inc',
		'aboutto'=>'step_aboutto.inc',
		'installer'=>'step_installer.inc',
		'finished'=>'step_finished.inc'
	);
	
	/** Virtual File System Folders/Directories
	  * @var array
	  */
	protected $vfsDir = array();
	
	/** Virtual File System Files
	  * @var array
	  */
	protected $vfsFiles = array();
	
	/** Virtual File System Sizes
	  * @var array
	  */
	protected $vfsSizes = array();
	
	/** Total size being packed
	  * @var int
	  */
	protected $vfsSize = 0;
	
	/** installerPageData
	  * @var string
	  */
	protected $pageData = '';
	
	/** installerPageData
	  * @var int
	  */
	protected $pageID = 1;
	
	/** installer forms
	  * @var array
	  */
	protected $pageHidden = array();
	
	/** Extra files to include in installer
	  * bring them up with installer.php?f=NAME
	  * add a file with the {@link phpInstaller::addMetaFile} method
	  * @var string
	  */
	protected $fileData = '';
	
	/** List of compressed files to download from the web
	  * @var array
	  */
	protected $download = array();
	
	/** List of decompression methods available
	  * @var array array([]=>'method')
	  */
	static $compresstypes = array('zip','tar','tgz');
	
	/** Compress the installer
	  * @var bool
	  */
	public $compress = true;
	
	/** Files to ignore
	  * if file contains any value in array
	  * @var array array([]=>'search')
	  */
	public $ignore = array();
	
	/** List of decompression methods that will be used
	  * @var array array([]=>'method')
	  */
	private $archives = array();
	
	/** List of write methods that will be used
	  * @var array array([]=>'method')
	  */
	private $writeMethod = array();
	
	/** Get a name of a resource file to be used
	  * @param string $name
	  * @return string file contents
	  */
	protected function file($name){
		if(!isset($this->files[$name])){die("No file with identifier $name. Aborting.");}
		$file = $this->dataDir.'/'.$this->files[$name];
		if(!is_file($file)){die("No file with name $file! Aborting.");}
		return file_get_contents($file);
	}
	
	/** Add a directory of files to the installer
	  * @param str $path file/directory to read from
	  * @param str $to path to extract to in installer
	  * @return void
	  */
	function addPath($path,$to='/'){
		$to = $this->stripPath("/$to");
		$this->message("Adding files from '$path' ==> '$to'...");
		$this->vfsPaths[$path] = $to;
		$this->_add_file($path,$to);
	}
	
	/** Add a path that the user downloads
	  * @param str $url Download URL
	  * @param str $to path to extract to
	  * @param str $type file type/extraction method to use
	  * @return bool
	  */
	function addPathDownload($url,$to='/',$type='zip',$required=false){
		if($this->addCompressionMethod($type)){
			$this->download[] = array(
				'required'=>true,
				'type'=>$type,
				'url'=>$url,
				'to'=>$to
			);
			$this->message("Adding download URL $url...");
			return true;
		}else return false;
	}
	
	
	/** Add a decompression method to use
	  * file "write.$method.inc" must exist in data directory
	  * called automaticly by {@link addPathDownload()}
	  * @param str $method
	  * @return bool
	  */
	function addCompressionMethod($method){
		if(is_file($this->dataDir."/extract.$method.inc")){
			if(!in_array($method,$this->archives)){
				$this->archives[] = $method;
			}
			$this->message("Using compression method $method...");
			return true;
		}else return false;
	}
	
	/**
	 * Add a method of creating files
	 * for example, over FTP, ect.
	 * the file method is enabled by default
	 *
	 * @param str $method file "write.$method.inc" must exist in data directory
	 * @return bool
	 */
	function addWriteMethod($method){
		if(is_file($this->dataDir."/write.$method.inc")){
			if(!in_array($method,$this->writeMethod)){
				$this->writeMethod[] = $method;
			}
			$this->message("Adding write method $method...");
			return true;
		}else return false;
	}
	
	/** is this an ignored file?
	  * @param str $fname file name
	  * @return bool true for ignored, false to include
	  */
	protected function ignored_file($fname){
		if(is_dir($fname)) $fname.='/';
		foreach($this->ignore as $value){
			$value = str_replace('\\','/',$value);
			$fname = str_replace('\\','/',$fname);
			if(strpos($fname,$value)!==false){
				return true;
			}
		}
		return false;
	}
	
	/** Add a file or directory to installer
	  * Use {@link addFile()} instead (it uses this)
	  *
	  * @param str $base main directory to read from
	  * @param str $to directory in installer to write to
	  * @param bool $output
	  * @return void
	  */
	protected function _add_file($from,$to,$output=true){
		$to = $this->stripPath($to);
		$from = $this->stripPath($from);
		
		if($this->ignored_file($from)){
			if($output) $this->message("    $from",2);//send ignored message
			return false;
		}
		if(is_file($from)){
			$this->vfsFiles[$to] = file_get_contents($from);
			$this->vfsSizes[$to] = strlen($this->vfsFiles[$from])+100;
			$this->vfsSize += $this->vfsSizes[$to];
			if($output) $this->message("    $from",3);
		}else if(is_dir($from) && $dh=opendir($from)){
			$this->vfsDir[] = $to;
			$this->vfsSize += 2;
			if($output) $this->message("    $from ($to)",4);
			while (($file = readdir($dh)) !== false) {
				if($file!='.' && $file!='..'){
					$this->_add_file("$from/$file","/$to/$file",$output);
				}
			}
			closedir($dh);
		}else{
			if($output) $this->message("... File $from is not recognised or non-existant");
		}
	}
	
	/** Compound a filename from a sentence
	  *
	  * @param str $name Input
	  * @param int $length max length of output
	  * @return str
	  */
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
	  * @param str $name
	  * @param str $filePath
	  * @param str $contentType
	  * @param array $replace
	  * @return bool
	  */
	function addMetaFile($name,$filePath,$contentType,$replace=array()){
		$name = $this->valid_chars($name);
		if(file_exists($filePath)){
			$data = file_get_contents($filePath);
			if($replace){
				$search = array();$replace = array();
				foreach($replace as $n=>$v){$search[]=$n;$replace[]=$v;}
				$data = str_replace($search,$replace,$data);
			}
			$data=base64_encode($data);
			$this->fileData.= "\ncase '$name':";
			$this->fileData.= "header('Content-Type: $contentType');";
			$this->fileData.= "echo base64_decode('".$data."');\n";
			$this->fileData.= "break;\n\n";
			return true;
		}else{
			return false;
		}
	}
	
	/** Add the file-copying steps to the installer
	 * @return void
	 */
	function addInstallerPages(){		
		$this->message("...Install Path ({$files['installpath']})");
		$this->addPage('Install Path',$this->file('installpath'),array(
			'path','method',
			'ftp_h','ftp_u','ftp_p',
			'sftp_h','sftp_u','sftp_p',
		));
		
		$this->message("...About to Install ({$files['aboutto']})");
		$this->addPage('About to Install',$this->file('aboutto'));
				
		$this->message("...Installer Page ({$files['installer']})");
		$this->installer_page = $this->addPage('Install',$this->file('installer'),array(),array('back'=>false,'disabled'=>true));
		$this->message("...Page $this->installer_page");
	}
	
	/** Generate the installer file
	  * @param str $output Path to write file
	  * @return void
	  */
	function generate($output='installer.php'){
		$files = &$this->files;
		$dataDir = $this->dataDir;
		$data1 = '';
		$alias = array();
		$classHeaders = '';
		
		$this->addWriteMethod('file');
		
		
		$classHeaders.= '$writeMethods = array();';
		foreach($this->writeMethod as $file){
			$this->message("Including extraction method 'write.$file.inc'...");
			$class = file_get_contents($dataDir.'/write.'.$file.'.inc');
			if(substr($class,0,5)=='<?php') $class=substr($class,6,-2);
			$classHeaders.= $class;
			$classHeaders.= "\$writeMethods['$file'] = 'write_$file';";
			
		}
		
		// load decompression functions for installer
		foreach($this->archives as $file){
			$this->message("Including extraction method 'extract.$file.inc'...");
			$extract = file_get_contents($dataDir.'/extract.'.$file.'.inc');
			if(substr($extract,0,6)=='#ALIAS'){
				list(,$type) = explode(' ',$extract);
				if($this->addCompressionMethod($type)){
					$this->message("Using Alias '$type'...");
					$alias[$file] = $type;
					$this->message("...Including extraction method 'extract.$type.txt' ...");
					$class = file_get_contents($dataDir.'/extract.'.$type.'.txt');
					if(substr($class,0,5)=='<?php') $class=substr($class,6,-2);
					$classHeaders.= $class;
				}else{
					$this->message("Invalid Alias '$type'");
					die;
				}
			}else{
				$alias[$file] = $file;
				$classHeaders.=$extract;
				continue;
			}
		}
		$this->message("Reading Data files...");
		$dataDir = realpath($dataDir);
		$replace = array(
			'PROGRAM'=>$this->appName
		);
		
		$data1.= $this->file('installerdata');
		$this->message('...Master Template');

		$this->message("...Finished Page ({$files['finished']})");
		$this->addPage('Finished',$this->file('finished'),array(),array('next'=>false));
		
		
		$this->message('Adding headers...');
		
		$data .= '<'.'?php error_reporting(E_ALL);';
		$data .= 'list(,$data)=explode("\n",file_get_contents(__FILE__),2);';
		//$data .= '$data=str_replace(array("/?","//"),array("?","/"),$data);';
		$data .= '$data=gzuncompress($data);';
		$data .= '$data=unserialize($data);';
		$data .= 'eval($data[0]);';
		$data .= 'die;';
		$data .= '__halt_co'.'mpiler();';
		$data .= ' ?'.'>';
		$data .= "\n";
		
		
		// #{CONSTANTS} are for blocks of code
		// CONSTANTS are replaced within an expression
		
		
		$this->message("Adding constants...");
		$ppath = $this->_get_name_from_str($this->appName,20);
		$this->message('...PPATH: '.$ppath);
		$data1 = str_replace('#{PAGES}',$this->pageData,$data1);
		$data1 = str_replace('PROGRAM',$this->appName,$data1);
		$data1 = str_replace('PPATH',$ppath,$data1);
		$data1 = str_replace('#{METAFILES}',$this->fileData,$data1);
		$data1 = str_replace('#{POSTINSTALL}',$this->postInstallEval,$data1);
		$data1 = str_replace('#{CLASS_HEADERS}',$classHeaders,$data1);
		$data1 = str_replace('INSTALLERSTEP',$this->installer_page,$data1);
		
		
		$actions = array();
		if($this->download){
			$this->message("Adding Downloads...");
			foreach($this->download as $dl){
				$this->message("... {$dl['url']}");
				//array(steps,type,attributes,from,to)
				$this->actions[] = array(1000,array(1000,2,$alias[$dl['type']],$dl['to'],$dl['url']));
			}
		}
		$steps = 0;
		$action = array();
		$padding = 20;
		$action[] = &$steps;
		if($this->vfsDir){
			$this->message("Adding Directories...");
			foreach($this->vfsDir as $v){
				$this->message("    $v");
				$action[] = array($padding,0,$v);
				$steps+=$padding;
			}
		}
		if($this->vfsFiles){
			$this->message("Compressing Files...");
			foreach($this->vfsSizes as $name=>$size){
				$this->message("    $name");
				//array(steps,type,attributes,from,to)
				$action[] = array($size+$padding,1,$name,$this->vfsFiles[$name]);
				//$action[] = array($size,1,$name,'');
				$steps+=$size+$padding;
			}
		}
		if($steps) $this->actions[] = $action;
		
		if(substr($data1,0,5)=='<?php') $data1=substr($data1,6,-2);
		
		if($this->compress){
			$this->message("Pre-Compressing...");
			$strlen=strlen($data1);
			$fp2 = fopen($fname=tempnam(dirname(__FILE__), "webinstall"),'w');
			fwrite($fp2,"<?php $data1 ?>");
			$data1=php_strip_whitespace($fname);
			$data1=substr($data1,6,-2);
			fclose($fp2);
			unlink($fname);
			message('...Made '.((1-(strlen($data1)/$strlen))*100).'% Smaller');
		}
		echo '<textarea>'.htmlspecialchars($data1).'</textarea>';
		
		$this->message("Post-Compressing...");
		$data1 = gzcompress(serialize(array($data1,$this->actions)),9);
		//$data1 .= str_replace(array('/','?'),array('//','/?'),$data1);
		$data .= $data1;
			
		
		//echo '<div style="height:10em;overflow:auto;border:inset 1px grey;">';
		//echo '<pre>';
		//foreach($this->vfsDir as $n=>$v) echo "$v/\n";
		//foreach($this->vfsFiles as $n=>$v) echo "\n$n";
		//echo '</pre>';
		//echo '</div>';
		
		$fo = fopen($output,'w');
		if($fo===false){$this->message("Writing to $output...",1);return false;}
		fwrite($fo,$data);
		fclose($fo);
		echo "<div><b>DONE! Installer is in file '$output'</b></div>";
		return true;
		}

	/** strip invalid chars from a string
	  * @param str $str Input string
	  * @param array $valid array of okay characters,
	  *              defaults to these: a-z A-z 0-9 _
	  * @return str stripped string
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
	
	/** Generate a tar/tgz/bz file
	 * If you define $installer, this function will also put in
	 * the archive a file (si)
	 *
	 * @param unknown_type $file
	 * @param unknown_type $format 
	 * @param bool $installer filename for an instaler file
	 */
	function generateTarGz($file,$format='gz',$installer=null){
		if(!class_exists('Archive_Tar')) include($this->dataDir.'/archive_tar.php');
		$cp = new Archive_Tar($file,$format);
		
        $v_result = true;
        if (!$cp->_openWrite()) return false;
        
		foreach($this->vfsFiles as $n=>$v)
			if(!$cp->_addString(substr($n,1),$v)) return false;
		
        $cp->_writeFooter();
        $cp->_close();
        
		return true;
	}
	
	/** Send a message to the installer console.
	  * @param str $message
	  * @param int $state prints short sucess/fail message after message
	  */
	function message($message,$state=0){
		$args = func_get_args();
		if($this->messageCallback) return call_user_func_array($this->messageCallback,$args);
		if($state==2) echo '<div style="color:grey;">';
		else echo '<div>';
		echo htmlspecialchars($message);
		if($state) echo '...';
		switch ($state) {
			case 1:echo '<span style="color:red;">failed</span>';break;
			case 2:echo '<span style="font-weight:bold;">ignored</span>';break;
			case 3:echo '<span style="color:green;">file</span>';break;
			case 4:echo '<span style="color:green;">dir</span>';break; 
			case 5:echo '<span style="color:green;">done</span>';break; 
		}
		echo '</div>';
	}
	
	/** add a page to the installer
	  * @param str $title Title of the page
	  * @param str $content PHP content
	  * @param array $fields list of input fields to use
	  * @param array $buttons status of buttons array('next'=>bool,'back'=>bool,'disabled'=>bool)
	  * @return int Page ID
	  */
	function addPage($title,$content,$fields=array(),$buttons=array()){
		if(!is_array($fields)) throw new Exception("Variable fields not an array!");
		if(!is_array($buttons)) throw new Exception("Variable buttons not an array!");
		if(!isset($buttons['next'])) $buttons['next']=true;
		if(!isset($buttons['back'])) $buttons['back']=true;
		if(!isset($buttons['disabled'])) $buttons['disabled']=false;
		
		$title = (string) $title;
		$page = $this->pageID++;
		
		if($page==1) $buttons['back']=false;
		
		$this->pageData.= "\$pages[$page]['title'] = ".var_export($title,true).";\n";
		$this->pageData.= "\$pages[$page]['disabled_buttons'] = ".($buttons['disabled']?'true':'false').";\n";
		$this->pageData.= "\$pages[$page]['button_next'] = ".($buttons['next']?'true':'false').";\n";
		$this->pageData.= "\$pages[$page]['button_back'] = ".($buttons['back']?'true':'false').";\n";
		$this->pageData.= "\$pages[$page]['uses'] = ".var_export($uses,true).";\n";
		$this->pageData.= 'if($step=='.$page.'){ ' . "\n";
		foreach($fields as $v) {
			$this->pageData .= '$uses[] = '.var_export($v,true). ";\n";
		}
		$this->pageData.= 'ob_start(); ?>' . "\n";
		$this->pageData.= $content;
		$this->pageData.= "\n<?php \$content .= ob_get_contents(); ob_end_clean(); }\n";
		foreach($fields as $v) {
			$this->pageData .= '$defaults['.var_export($v,true). "] = '';\n";
		}
		$this->compress = false;
		return $page;
	}
	
	/** set the engine data directory
	  * @param str $path File path
	  * @return void
	  */
	function dataDir($path){
		$path = realpath($path);
		if(!is_dir($path)) return false;
		$this->dataDir = realpath($path);
		return true;
	}
	
	/** strip extra slashes out of a file path
	  * @param str $path
	  * @return str
	  */
	function stripPath($path){
		$path = explode('/', $path);
		$result=array();
		if (!$path[0]) $result[] = '';
		foreach ($path AS $key => $dir) {
			if ($dir == '..') {
				if (end($result) == '..') $result[] = '..';
				else if (!array_pop($result)) $result[] = '..';
			} else if ($dir && $dir != '.') $result[] = $dir;
		}
		if (!end($path)) $result[] = '';
		return implode('/', $result);
	}
	
}
?>