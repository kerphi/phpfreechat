<?php
/**
 * phpfreechati18n.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

function __($str)
{
  return (!empty($GLOBALS['i18n'][$str])) ? $GLOBALS['i18n'][$str] : $str;
}

class phpFreeChatI18N
{
  function Init($lang)
  {
    require_once(dirname(__FILE__)."/../i18n/".$lang."/main.php");
  }

  /**
   * Parse the source-code and update the i18n ressources files
   */
  function UpdateMessageRessources()
  {
    $src_filenames = array( dirname(__FILE__)."/phpfreechat.class.php",
                            dirname(__FILE__)."/phpfreechattools.class.php",
                            dirname(__FILE__)."/phpfreechatconfig.class.php",
                            dirname(__FILE__)."/phpfreechatcontainer.class.php",
                            dirname(__FILE__)."/phpfreechatcontainerfile.class.php" );
    $res = array();
    foreach ( $src_filenames as $src_filename )
    {
      $lines = file($src_filename);
      $line_nb = 1;
      foreach( $lines as $l)
      {
        if( preg_match_all('/__\("(.*)"\)/', $l, $matches) )
        {
          //          echo "line: ".$line_nb."\t- ".$matches[1][0]."\n";
          $res[$matches[1][0]] = "// line ".$line_nb." in ".basename($src_filename);
        }
        $line_nb++;
      }
    }
    
    $dst_filenames = array( dirname(__FILE__)."/../i18n/fr/main.php",
                            dirname(__FILE__)."/../i18n/en/main.php");
    foreach( $dst_filenames as $dst_filename )
    {
      // filter lines to keep, line to add
      $old_content = file_get_contents($dst_filename);
      // remove php tags to keep only real content
      $old_content = preg_replace("/^\<\?php/", "", $old_content);
      $old_content = preg_replace("/\?\>$/", "", $old_content);
      
      // save into the file
      $new_content = "";
      foreach($res as $str => $com)
      {
        if (preg_match("/".preg_quote($str)."/", $old_content) == 0)
          $new_content .= $com."\n\$GLOBAL[\"i18n\"][\"".$str."\"] = \"\";\n\n";
      }
      $content = "<?php" . $old_content . $new_content . "?>";
      //echo $content;
      
      file_put_contents($dst_filename, $content);
    }
  }
}

?>