<?php
# lang
require_once("../src/pfci18n.class.php");
require_once("inc.conf.php");
pfcI18N::Init($lang,"admin");


# version class
require_once("version.class.php");
$version = new version();
?>

<?php
// TOP //
include("index_html_top.php");
?>

<div class="content">
  <h2><?php echo _pfc("Administration"); ?></h2>

<?php
if ($version->getPFCOfficialCurrentVersion()==0){
?>
  <div><h3><?php echo _pfc("Internet connection is not possible"); ?></h3>
    <ul>
      <li><?php echo _pfc("PFC version"); ?> : <?php echo $version->getLocalVersion(); ?></li>
    </ul>
  </div>

<?php
}
elseif (($version->getLocalVersion())==($version->getPFCOfficialCurrentVersion())){
?>

  <div class="ok"><h3><img src="style/check_on.png" alt="<?php echo _pfc("PFC is update"); ?>"> <?php echo _pfc("PFC is update"); ?></h3>
    <ul>
      <li><?php echo _pfc("PFC version"); ?> : <?php echo $version->getLocalVersion(); ?></li>
    </ul>
  </div>

<?php
}
else{
?>
  <div class="ko"><h3><img src="style/check_off.png" alt="<?php echo _pfc("PFC is not update"); ?>"> <?php echo _pfc("PFC is not update"); ?></h3>
    <ul>
      <li><?php echo _pfc("Your version"); ?> : <?php echo $version->getLocalVersion(); ?></li>
      <li><?php echo _pfc("The last official version"); ?> : <?php echo $version->getPFCOfficialCurrentVersion(); ?></li>
      <li><?php echo _pfc("Download the last version %s here %s.","<a href=\"http://sourceforge.net/project/showfiles.php?group_id=158880\">","</a>"); ?></li>
    </ul>
  </div>

<?php
}  
?>  
</div>

<?php
// BOTTOM
include("index_html_bottom.php");
?>