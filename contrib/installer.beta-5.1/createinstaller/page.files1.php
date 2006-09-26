<?php
$uses[] = 'rootpath';
$uses[] = 'packages';
$packages = gpv('packages');
function genSelect($type=null){
	$compresstypes='';
	foreach(phpInstaller::$compresstypes as $v){
		if($v==$type) $compresstypes.="<option selected=\"selected\">$v</option>";
		else $compresstypes.="<option>$v</option>";
	}
	return $compresstypes;
}
?>
<h2>Files to Extract</h2>
<p>
Here you can add files that will be installed.
Optional if you do not need files installed, or files are already distributed.
</p>
<div>
<select name="package_type" id="package_type">
<option>Regluar</option>
<option>Download</option>
<option>Archive (Distributed With Installer)</option>
</select>
<input type="submit" name="package_add" id="package_add" value="Add..."></div>
<?php
if(isset($_POST['package_type']) && isset($_POST['package_add'])){
	switch ($_POST['package_type']){
		case 'Regluar':
			$packages[] = array('to'=>'/','path'=>dirname(__FILE__));
			break;
		case 'Download':
			$packages[] = array('to'=>'/','url'=>'http://domain/file.zip ; http://domain/backup.zip','type'=>'zip');
			break;
		case 'Archive (Distributed With Installer)':
			$packages[] = array('to'=>'/','path'=>'archive.zip','type'=>'zip');
			break;
	}
}

$_packages = array();
foreach($packages as $x=>$v){
	if(!isset($_POST['package_clear'][$x])) $_packages[] = $v;
}
$packages = $_packages;

?>
<h2>Current Paths</h2>
<?php
if($packages){
?>
<table style="width:100%;">
<colgroup>
<col width="20%" />
<col width="30%" />
<col width="35%" />
<col width="10%" />
<col width="5%" />
</colgroup>
<thead><th>Path To</th><th>Path From</th><th>URL (seperate with a " ; ", a space, semicolon, and another space)</th><th>Type</th><th>Remove</th></thead>
<?php
	foreach($packages as $n=>$v){
		echo '<tr>';
		echo '<td>';
		echo '<input type="text" style="width:100%;" name="packages['.$n.'][to]" id="packages['.$n.'][to]" value="'.$v['to'].'" >';
		echo '</td><td>';
		echo isset($v['path'])?'<input style="width:100%;" type="text" name="packages['.$n.'][path]" id="packages['.$n.'][path]" value="'.$v['path'].'" >':'N/A';
		echo '</td><td>';
		echo isset($v['url'])?'<input style="width:100%;" type="text" name="packages['.$n.'][url]" id="packages['.$n.'][url]" value="'.$v['url'].'" >':'N/A';
		echo '</td><td>';
		echo isset($v['type'])?'<select style="width:100%;" name="packages['.$n.'][type]" id="packages['.$n.'][type]">'.genSelect($v['type']).'</select>':'N/A';
		echo '</td><td>';
		echo '<input type="submit" name="package_clear['.$n.']" id="package_clear['.$n.']" value="Remove" >';
		echo '</td>';
		echo "</tr>\n";
	}
	echo '</table>';
}else{
	echo 'You have no listings defined.';
}
?>