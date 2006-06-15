<?php

$delim = DIRECTORY_SEPARATOR == "\\" ? ";" : ":";
$classpath = "." . $delim . dirname(__FILE__).'/../lib/pear/';
ini_set('include_path', $classpath);
require_once "PHPUnit.php";

class pfcContainerTestcase extends PHPUnit_TestCase
{
  var $type   = "";
  
  var $chan   = "testcase";
  var $nick   = "testnick";
  var $nickid = "testnickid";

  var $c  = NULL;
  var $ct = NULL;
  
  // constructor of the test suite
  function pfcContainerTestcase($name)
  {
    $this->PHPUnit_TestCase($name);
  }
  
  // called before the test functions will be executed
  // this function is defined in PHPUnit_TestCase and overwritten
  // here
  function setUp()
  {
    require_once dirname(__FILE__)."/../src/pfcglobalconfig.class.php";   
    $params = array();
    $params["title"] = "testcase -> pfccontainer_".$this->type;
    $params["serverid"] = md5(__FILE__ . time());
    $params["container_type"] = $this->type;
    $this->c  =& pfcGlobalConfig::Instance($params);
    $this->ct =& $this->c->getContainerInstance();
  }

  // called after the test functions are executed
  // this function is defined in PHPUnit_TestCase and overwritten
  // here
  function tearDown()
  {
    $this->ct->clear();
    $this->c->destroyCache();
  }

  function testCreateNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // create on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertTrue($isonline, "nickname should be online on the channel");

    // create on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertTrue($isonline, "nickname should be online on the server");
  }

  function testRemoveNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    $this->ct->removeNick($chan, $nick);
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertFalse($isonline, "nickname shouldn't be online on the channel");   

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $this->ct->removeNick($chan, $nick);
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertFalse($isonline, "nickname shouldn't be online on the server");    
  }
  
  function testGetNickId_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;
 
    $this->ct->createNick(NULL, $nick, $nickid);
    $ret = $this->ct->getNickId($nick);
    $this->assertEquals($nickid, $ret, "created nickname doesn't have a correct nickid");
  }
  
  function testRemoveObsoleteNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    sleep(2);
    $ret = $this->ct->removeObsoleteNick($chan, 1000);
    $this->assertEquals(count($ret), 1, "1 nickname should be obsolete");
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertFalse($isonline, "nickname shouldn't be online anymore");
    
    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    sleep(2);
    $ret = $this->ct->removeObsoleteNick($chan, 1000);
    $this->assertEquals(count($ret), 1, "1 nickname should be obsolete");
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertFalse($isonline, "nickname shouldn't be online anymore");
  }
  
  function testSetGetRmMeta_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // set / get
    $this->ct->setMeta($nickid, "key1", "nickname", $nick);
    $metadata = $this->ct->getMeta("key1", "nickname", $nick);
    $this->assertEquals($nickid, $metadata, "metadata value is not correct");

    // set / rm / get
    $this->ct->setMeta($nickid, "key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertEquals($nickid, $metadata, "metadata value is not correct");
    $this->ct->rmMeta("key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertNull($metadata, "metadata should not exists anymore");

    // set / rm (all) / get
    $this->ct->setMeta($nickid, "key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertEquals($nickid, $metadata, "metadata value is not correct");
    $this->ct->rmMeta(NULL, "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertNull($metadata, "metadata should not exists anymore");
    $metadata = $this->ct->getMeta("key1", "nickname", $nick);
    $this->assertNull($metadata, "metadata should not exists anymore");
  }

  function testupdateNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    sleep(2);
    $ret = $this->ct->updateNick($chan, $nick);
    $this->assertTrue($ret, "nickname should be correctly updated");
    $ret = $this->ct->removeObsoleteNick($chan, 1000);
    $this->assertFalse(in_array($nick, $ret), "nickname shouldn't be removed because it has been updated");
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertTrue($isonline, "nickname should be online");
    
    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    sleep(2);
    $ret = $this->ct->updateNick($chan, $nick);
    $this->assertTrue($ret, "nickname should be correctly updated");
    $ret = $this->ct->removeObsoleteNick($chan, 1000);
    $this->assertFalse(in_array($nick, $ret), "nickname shouldn't be removed because it has been updated");
    $isonline = ($this->ct->isNickOnline($chan, $nick) >= 0);
    $this->assertTrue($isonline, "nickname should be online");
  }

  function testchangeNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick1  = $this->nick;
    $nick2  = $this->nick."2";
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // create a nick on a channel and change it
    $this->ct->createNick($chan, $nick1, $nickid);
    $ret = $this->ct->changeNick($nick2, $nick1);
    $this->assertTrue($ret, "nickname change function should returns true (success)");
    $isonline1 = ($this->ct->isNickOnline($chan, $nick1) >= 0);
    $isonline2 = ($this->ct->isNickOnline($chan, $nick2) >= 0);
    $this->assertFalse($isonline1, "nickname shouldn't be online");
    $this->assertTrue($isonline2, "nickname shouldn't be online");
  }
  
  function testwrite_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;
    $cmd    = "send";
    $msg    = "my test message";
    
    // create message on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    $msgid = $this->ct->write($chan, $nick, $cmd, $msg);
    $this->assertEquals($msgid, 1,"generated msg_id is not correct");
    $res = $this->ct->read($chan, 0);
    $this->assertEquals(1, count($res["data"]), "1 messages should be read");
    $this->assertEquals($msg, $res["data"][1]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 1 ,"new_from_id is not correct");

    // create message on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $msgid = $this->ct->write($chan, $nick, $cmd, $msg);
    $this->assertEquals($msgid, 1,"generated msg_id is not correct");
    $res = $this->ct->read($chan, 0);
    $this->assertEquals(1, count($res["data"]), "1 messages should be read");
    $this->assertEquals($msg, $res["data"][1]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 1 ,"new_from_id is not correct");
  }
  
  function testread_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;
    $cmd    = "send";
    $msg    = "my test message";
    
    // create on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1, "generated msg_id is not correct");
    }

    $res = $this->ct->read($chan, 0);
    $this->assertEquals(10, count($res["data"]), "10 messages should be read");
    $this->assertEquals($msg."0", $res["data"][1]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($msg."8", $res["data"][9]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 10 ,"new_from_id is not correct");
    
    $res = $this->ct->read($chan, 5);
    $this->assertEquals(5, count($res["data"]), "5 messages should be read");
    $this->assertEquals($msg."5", $res["data"][6]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($msg."9", $res["data"][10]["param"] ,"messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 10 ,"new_from_id is not correct");
  }

  function testgetLastId_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nickid = $this->nickid;
    $chan   = $this->chan;
    $cmd    = "send";
    $msg    = "my test message";
    
    // on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1,"generated msg_id is not correct");
    }
    $msgid = $this->ct->getLastId($chan);
    $this->assertEquals(10, $msgid, "last msgid is not correct");

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1,"generated msg_id is not correct");
    }
    $msgid = $this->ct->getLastId($chan);
    $this->assertEquals(10, $msgid, "last msgid is not correct");
  }
}

?>
