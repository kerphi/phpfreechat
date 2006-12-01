<?php
/**
 * Dice rolling, 
 * test routines at the end of this file
 *
 * @author   Alessandro Pasotti www.itopen.it
 * @copyright (C) itOpen 2006
 * @licence  LGPL
 * 
 * Valid strings:
 * xdx
 * xdxx
 * xdxxx
 * xdxxx+x
 * xdxxx-x
 */

class Dice {

  var $command;

  function check($text){
    $this->errors = array();
    $this->command= '';
    if(preg_match('/^([0-9]+)d([0-9]{1,3})([\+-][0-9]+)?$/', $text, $matches)){
      $this->command['launch'] = (int) $matches[1];
      $this->command['faces']  = (int) $matches[2];
      // Now go for corrections
      if(count($matches) == 4){
        $this->command['bias'] = $matches[3];
      }
      if(!($this->command['launch'] && $this->command['faces'])){
        //print_r($matches);
        $this->errors[] = "Be serious, not null dice please.";
        return false;
      }
    } else {
      //print_r($matches);
      // Too long
      //$this->errors[] = "'$text' is not a valid string for a dice launch. Valid strings match the following patterns xdyyy, xdyyy+z or xdyyy-z where x,  y and z are digits, you can have up to three y.";
      $this->errors[] = 'Not valid. Valid launches are like xdyyy';
      return false;
    }
    $this->text    = $text;
    srand((double)microtime()*1000000);
    return true;
  }

  function roll(){
    $sum    = 0;
    $result = $this->text . ' &#187; ' ;  
    for($i = 0; $i < $this->command['launch']; $i++){
      $launchresult  = rand(1, $this->command['faces']);
      $sum          += $launchresult;
      $result       .= ' + ' . $launchresult;
    }
    if(count($this->command) == 3){
      $sum          += $this->command['bias'];
      $result       .= ' [' . $this->command['bias'] . ']';
    }
    return $result . ' = ' . '<strong>' . $sum . '</strong>';
  }

  function error_get(){
    if(!count($this->errors)){
      return '';
    } else {
      return join("<br />\n", $this->errors);
    }
  }

  function test(){
    // Valid
    $testvalid = array(
        '1d1'
      , '2d2'
      , '9d6'
      , '1d99'
      , '1d999'
      , '1d100'
      , '1d6+1'
      , '1d6-9'
    );
    
    // Not valid
    $testnotvalid = array(
        '0d6'
      , '99d6'
      , '1d1000'
      , '1d000'
      , '1d000'
      , '1d6+99'
      , '1d6+10'
      , 'xad--'
    );
    
     print "<br />\n---------------------------------------<br />\n";
     print "Dice: testing valid launches" . "<br />\n";
     $valid = 0;
     foreach($testvalid as $t){
       if($this->check($t)){
         $valid ++;
         print $this->roll() . "\n";
       } else {
         print $this->error_get(). "\n";
       }
     }
     print "<br />\n" . "Valid launches: " . $valid . '/' . count($testvalid) . "<br />\n";

     print "<br />\n" . "Dice: testing notvalid launches" . "<br />\n";
     $valid = 0;
     foreach($testnotvalid as $t){
       if($this->check($t)){
         $valid ++;
         print $this->roll() . "\n";
       } else {
         print $this->error_get(). "\n";
       }
       print "---------------------------------------<br />\n";
     }
     print "<br />\n" . "Notvalid launches: " . (count($testnotvalid) - $valid) . '/' . count($testnotvalid) . "<br />\n";

   }
  
}

/*
* Uncomment for testing
*/

/*/
  $d = new Dice();
  $d->test();
//*/

?>