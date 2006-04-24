<?

/**
* Class to manage theme of pfc
* @author Fred Delaunay <fred@nemako.net>
*/

class version{
    var $local_version; // file of the user version
    var $pfc_official_current_version; // file of the pfc official current version

    
    function version(){
       $this->local_version = dirname(__FILE__)."/../version";
       $this->pfc_official_current_version = "http://www.phpfreechat.net/version";
    }

    /**
    * Get the local version
    * @return integer version
    */
    function getLocalVersion(){
       $fp =  fopen($this->local_version,"r");
       $version =  trim(fgets($fp));
       fclose($fp);
       return $version;
    }

    /**
    * Get the pfc official current version
    * @return integer version
    */
    function getPFCOfficialCurrentVersion(){
       if (file_exists($this->pfc_official_current_version)) {
         $fp =  fopen($this->pfc_official_current_version,"r");
         $version =  trim(fgets($fp));
         fclose($fp);
         return $version;
       }
       else
         return 0;
    }
 
}

?>