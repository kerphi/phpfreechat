<?php

require_once "container_generic.php";

class pfcContainerTestcase_File extends pfcContainerTestcase
{
  // constructor of the test suite
  function pfcContainerTestcase_File($name)
  {
    $this->type = "File";
    $this->pfcContainerTestcase($name);
  }
  
  // called before the test functions will be executed
  // this function is defined in PHPUnit_TestCase and overwritten
  // here
  function setUp()
  {
    pfcContainerTestcase::setUp();
  }

  // called after the test functions are executed
  // this function is defined in PHPUnit_TestCase and overwritten
  // here
  function tearDown()
  {
    pfcContainerTestcase::tearDown();   
  }

  // this is a specific test for the File container
  function testCreateNick_File()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    $ct->createNick($chan, $nick, $nickid);
    
    $nick_dir = ($chan != NULL) ? $c->container_cfg_channel_dir."/".$ct->_encode($chan)."/nicknames" : $c->container_cfg_server_dir."/nicknames";  
    $nick_filename = $nick_dir."/".$ct->_encode($nick);

    $this->assertTrue(file_exists($nick_filename), "nickname file doesn't exists");
    $this->assertEquals(file_get_contents($nick_filename), $nickid, "nickname file doesn't contains correct nickid");
  }
}

// on desactive le timeout car se script peut mettre bcp de temps a s'executer
ini_set('max_execution_time', -1);

$suite = new PHPUnit_TestSuite();
$suite->addTestSuite("pfcContainerTestcase_File");
$result =& PHPUnit::run($suite);
echo "<pre>";
print_r($result->toString());
echo "</pre>";

?>