<?php
/**
 * pfci18n.class.php
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
 
require_once(dirname(__FILE__)."/pfctools.php");

function _pfc()
{
  $args = func_get_args();
  $serverid = isset($GLOBALS['serverid']) ? $GLOBALS['serverid'] : 0; // serverid is used to avoid conflicts with external code using same 'i18n' key
  $args[0] = isset($GLOBALS[$serverid]["i18n"][$args[0]]) && $GLOBALS[$serverid]["i18n"][$args[0]] != "" ?
    ($GLOBALS["output_encoding"] == "UTF-8" ?
       $GLOBALS[$serverid]["i18n"][$args[0]] :
       iconv("UTF-8", $GLOBALS["output_encoding"], $GLOBALS[$serverid]["i18n"][$args[0]])) :
    "_".$args[0]."_";
  return call_user_func_array('sprintf', $args);
}
/**
 * Just like _pfc but just return the raw translated string, keeping the %s into it
 * (used by the javascript resources (i18n) class)
 */
function _pfc2()
{
  $args = func_get_args();
  $serverid = isset($GLOBALS['serverid']) ? $GLOBALS['serverid'] : 0; // serverid is used to avoid conflicts with external code using same 'i18n' key
  $args[0] = isset($GLOBALS[$serverid]["i18n"][$args[0]]) && $GLOBALS[$serverid]["i18n"][$args[0]] != "" ?
    ($GLOBALS["output_encoding"] == "UTF-8" ?
       $GLOBALS[$serverid]["i18n"][$args[0]] :
       iconv("UTF-8", $GLOBALS["output_encoding"], $GLOBALS[$serverid]["i18n"][$args[0]])) :
    "_".$args[0]."_";
  return $args[0];
}

class pfcI18N
{
  static function Init($language,$type="main")
  {
    if ($type=="admin")
      if (!in_array($language, pfcI18N::GetAcceptedLanguage("admin")))
        $language = pfcI18N::GetDefaultLanguage();
    if (!in_array($language, pfcI18N::GetAcceptedLanguage()))
      $language = pfcI18N::GetDefaultLanguage();
    
    if ($type=="admin")
      require_once(dirname(__FILE__)."/../i18n/".$language."/admin.php");
    else
      require_once(dirname(__FILE__)."/../i18n/".$language."/main.php");

    $serverid = isset($GLOBALS['serverid']) ? $GLOBALS['serverid'] : 0; // serverid is used to avoid conflicts with external code using same 'i18n' key
    $GLOBALS[$serverid]['i18n'] = $GLOBALS['i18n']; // do not pass by reference because $GLOBALS['i18n'] is maybe used by unknown external code
    
    $GLOBALS["output_encoding"] = "UTF-8"; // by default client/server communication is utf8 encoded
  }

  /**
   * Switch output encoding in order to write the right characteres in the web page
   */
  static function SwitchOutputEncoding($oe = "")
  {
    if ($oe == "")
    {
      $GLOBALS["output_encoding"]     = $GLOBALS["old_output_encoding"];
      unset($GLOBALS["old_output_encoding"]);
    }
    else
    {
      if (isset($GLOBALS["old_output_encoding"]))
        die("old_output_encoding must be empty (".$GLOBALS["old_output_encoding"].")");
      $GLOBALS["old_output_encoding"] = $GLOBALS["output_encoding"];
      $GLOBALS["output_encoding"]     = $oe;
    }
  }
  
  /**
   * Return the default language : "en"
   */
  static function GetDefaultLanguage()
  {
    return "en_US";
  }

  /**
   * Return the language list supported bye i18n system
   * (content of the i18n directory)
   * fix for the slovak language UTAN
   */
  static function GetAcceptedLanguage($type="main")
  {
    return /*<GetAcceptedLanguage>*/array('nl_NL','ko_KR','nl_BE','tr_TR','pt_PT','en_US','eo','hr_HR','vi_VN','es_ES','zh_TW','nn_NO','ru_RU','id_ID','hu_HU','th_TH','hy_AM','oc_FR','da_DK','de_DE-formal','uk_RO','nb_NO','fr_FR','it_IT','sv_SE','uk_UA','sr_CS','ar_LB','bg_BG','pt_BR','ba_BA','bn_BD','el_GR','zh_CN','gl_ES','pl_PL','de_DE-informal','ja_JP','sk_SK');/*</GetAcceptedLanguage>*/
  }
  
  /**
   * Parse the source-code and update the i18n ressources files
   */
  static function UpdateMessageRessources()
  {
    // first of all, update the GetAcceptedLanguage list
    $i18n_basepath = dirname(__FILE__).'/../i18n';
    $i18n_accepted_lang = array();
    $dh = opendir($i18n_basepath);
    while (false !== ($file = readdir($dh)))
    {
      // skip . and .. generic files, skip also .svn directory
      if ($file == "." || $file == ".." || strpos($file,".")===0) continue;
      if (file_exists($i18n_basepath.'/'.$file.'/main.php')) $i18n_accepted_lang[] = $file;
    }
    closedir($dh);
    $i18n_accepted_lang_str = "array('" . implode("','", $i18n_accepted_lang) . "');";
    $data = file_get_contents_flock(__FILE__);
    $data = preg_replace("/(\/\*<GetAcceptedLanguage>\*\/)(.*)(\/\*<\/GetAcceptedLanguage>\*\/)/",
                         "$1".$i18n_accepted_lang_str."$3",
                         $data);
    file_put_contents(__FILE__, $data, LOCK_EX);

    // Now scan the source code in order to find "_pfc" patterns
    $files = array();
    $files = array_merge($files, glob(dirname(__FILE__)."/*.php"));
    $files = array_merge($files, glob(dirname(__FILE__)."/commands/*.php")); 
    $files = array_merge($files, glob(dirname(__FILE__)."/containers/*.php"));
    $files = array_merge($files, glob(dirname(__FILE__)."/proxies/*.php"));    
    $files = array_merge($files, glob(dirname(__FILE__)."/client/*.php"));
    $files = array_merge($files, glob(dirname(__FILE__)."/../themes/default/*.php"));
    $res = array();
    foreach ( $files as $src_filename )
    {
      $lines = file($src_filename);
      $line_nb = 1;
      foreach( $lines as $l)
      {
        // the labels server side
        if( preg_match_all('/_pfc\("([^\"]+)"/', $l, $matches) )
        {
          foreach($matches[1] as $label)
          {
            echo "line: ".$line_nb."\t- ".$label."\n";
            $res[$label] = "// line ".$line_nb." in ".basename($src_filename);
          }
        }
        // the labels client side (JS)
        if( preg_match_all('/"([^"]*)",\s\/\/\s_pfc/', $l, $matches) )
        {
          echo "line: ".$line_nb."\t- ".$matches[1][0]."\n";
          $res[$matches[1][0]] = "// line ".$line_nb." in ".basename($src_filename);
        }
        $line_nb++;
      }
    }

    $dst_filenames = array();
    foreach($i18n_accepted_lang as $lg)
      $dst_filenames[] = dirname(__FILE__)."/../i18n/".$lg."/main.php";

    foreach( $dst_filenames as $dst_filename )
    {
      // filter lines to keep, line to add
      $old_content = file_get_contents_flock($dst_filename);
      // remove php tags to keep only real content
      $old_content = preg_replace("/^\<\?php/", "", $old_content);
      $old_content = preg_replace("/\?\>$/", "", $old_content);
      
      // save into the file
      $new_content = "";
      foreach($res as $str => $com)
      {
        //echo "com=".$com."\n";
        //echo "str=".$str."\n";
        if (preg_match("/".preg_quote($str,'/')."/", $old_content) == 0)
          $new_content .= $com."\n\$GLOBALS[\"i18n\"][\"".$str."\"] = \"\";\n\n";
      }
      $content = "<?php" . $old_content . $new_content . "?>";
      //echo $content;
      
      file_put_contents($dst_filename, $content, LOCK_EX);
    }
  }
}

?>
