<?
# lang
require_once("../src/pfci18n.class.php");
require_once("inc.conf.php");
pfcI18N::Init($lang,"admin");


# version class
require_once("version.class.php");
$version = new version();
?>

<?
// TOP //
include("index_html_top.php");
?>

<div class="content">
  <h2><? echo _pfc("Administration"); ?></h2>

<?
if ($version->getPFCOfficialCurrentVersion()==0){
?>
  <div><h3><? echo _pfc("Internet connection is not possible"); ?></h3>
    <ul>
      <li><? echo _pfc("PFC version"); ?> : <? echo $version->getLocalVersion(); ?></li>
    </ul>
  </div>

<?
}
elseif (($version->getLocalVersion())==($version->getPFCOfficialCurrentVersion())){
?>

  <div class="ok"><h3><img src="style/check_on.png" alt="<? echo _pfc("PFC is update"); ?>"> <? echo _pfc("PFC is update"); ?></h3>
    <ul>
      <li><? echo _pfc("PFC version"); ?> : <? echo $version->getLocalVersion(); ?></li>
    </ul>
  </div>

<?
}
else{
?>
  <div class="ko"><h3><img src="style/check_off.png" alt="<? echo _pfc("PFC is not update"); ?>"> <? echo _pfc("PFC is not update"); ?></h3>
    <ul>
      <li><? echo _pfc("Your version"); ?> : <? echo $version->getLocalVersion(); ?></li>
      <li><? echo _pfc("The last official version"); ?> : <? echo $version->getPFCOfficialCurrentVersion(); ?></li>
      <li><? echo _pfc("Download the last version %s here %s.","<a href=\"http://sourceforge.net/project/showfiles.php?group_id=158880\">","</a>"); ?></li>
    </ul>
  </div>

<?
}  
?>  
</div>

<?
// BOTTOM
include("index_html_bottom.php");
?>