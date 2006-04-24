<?php
require_once("themes.class.php");
$themes = new themes();


?>

<?php
// TOP //
include("index_html_top.php");
?>

<div class="content">
  <h2>Liste des themes disponibles</h2>
<?php
  echo "<ul>";
  $themes_list = $themes->getThemesList();
  for($i=0;$i<count($themes_list);$i++) {
    $author = $themes->getThemeAuthor($themes_list[$i]);
    $website = $themes->getThemeWebsite($themes_list[$i]);
    
    echo "<li><strong>$themes_list[$i]</strong>";
    if ($author!='0' || $website!='0') echo " ( $author - <a href=\"$website\">$website</a> )";
    echo "</li>";
    echo "<ul>";


    

    if($themes->isThemeImages($themes_list[$i]))
       echo "<li>Images <img src=\"style/check_on.png\" alt=\"On\" /></li>";
    else
       echo "<li>Images <img src=\"style/check_off.png\" alt=\"Off\" /></li>";
    
    if($themes->isThemeSmiley($themes_list[$i]))
       echo "<li>Smiley <img src=\"style/check_on.png\" alt=\"On\" /></li>";
    else
       echo "<li>Smiley <img src=\"style/check_off.png\" alt=\"Off\" /></li>";
       
    if($themes->isThemeTemplates($themes_list[$i])){
       echo "<li>Templates <img src=\"style/check_on.png\" alt=\"On\" /></li>";
       $templates_files_list = $themes->getThemesTemplatesFilesList($themes_list[$i]);
       echo "<ul>";
       for($j=0;$j<count($templates_files_list);$j++) {
         echo "<li>$templates_files_list[$j]</li>";
       }
       echo "</ul>";
    }
    else
       echo "<li>Templates <img src=\"style/check_off.png\" alt=\"Off\" /></li>";
              
    echo "</ul>";
  }
  echo "</ul>";
?>
</div>

<?php
// BOTTOM
include("index_html_bottom.php");
?>