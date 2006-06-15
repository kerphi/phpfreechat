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
}

// on desactive le timeout car se script peut mettre bcp de temps a s'executer
ini_set('max_execution_time', 0);

$suite = new PHPUnit_TestSuite();
$suite->addTestSuite("pfcContainerTestcase_File");
$result =& PHPUnit::run($suite);
echo "<pre>";
print_r($result->toString());
echo "</pre>";

?>