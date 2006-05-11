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
    $ct =& $this->ct;
    // remove the created files and directories
    $this->ct->clear();    
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
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the channel");

    // create on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the server");
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
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the channel");
    $this->ct->removeNick($chan,$nick);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertFalse(in_array($nick, $online_nick), "nickname should not be online on the channel");   

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the server");
    $this->ct->removeNick($chan,$nick);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertFalse(in_array($nick, $online_nick), "nickname should not be online on the server");   
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
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the channel");
    sleep(2);
    $ret = $this->ct->removeObsoleteNick($chan, "1000");
    $this->assertTrue(in_array($nick, $ret), "nickname should be removed from the channel");
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertFalse(in_array($nick, $online_nick), "nickname should not be online on the channel");

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "nickname should be online on the server");
    sleep(2);
    $ret = $this->ct->removeObsoleteNick($chan, "1000");
    $this->assertTrue(in_array($nick, $ret), "nickname should be removed from the server");
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertFalse(in_array($nick, $online_nick), "nickname should not be online on the server");
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
    $this->assertEquals($nickid, $metadata, "1-metadata value is not correct");

    // set / rm / get
    $this->ct->setMeta($nickid, "key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertEquals($nickid, $metadata, "2-metadata value is not correct");
    $this->ct->rmMeta("key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertNull($metadata, "3-metadata should not exists anymore");

    // set / rm (all) / get
    $this->ct->setMeta($nickid, "key2", "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertEquals($nickid, $metadata, "4-metadata value is not correct");
    $this->ct->rmMeta(NULL, "nickname", $nick);
    $metadata = $this->ct->getMeta("key2", "nickname", $nick);
    $this->assertNull($metadata, "5-metadata should not exists anymore");
    $metadata = $this->ct->getMeta("key1", "nickname", $nick);
    $this->assertNull($metadata, "6-metadata should not exists anymore");
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
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "1-nickname should be online on the channel");
    sleep(2);
    $ret = $this->ct->updateNick($chan, $nick);
    $this->assertTrue($ret, "2-nickname should be correctly updated on the channel");
    $ret = $this->ct->removeObsoleteNick($chan, "1000");
    $this->assertFalse(in_array($nick, $ret), "3-nickname should not be removed from the channel because it has been updated");
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "4-nickname should be online on the channel");

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "5-nickname should be online on the server");
    sleep(2);
    $ret = $this->ct->updateNick($chan, $nick);
    $this->assertTrue($ret, "6-nickname should be correctly updated on the server");
    $ret = $this->ct->removeObsoleteNick($chan, "1000");
    $this->assertFalse(in_array($nick, $ret), "7-nickname should not be removed from the server because it has been updated");
    $online_nick = $this->ct->getOnlineNick($chan);
    $this->assertTrue(in_array($nick, $online_nick), "8-nickname should be online on the server");
  }

  function testchangeNick_Generic()
  {
    $c  =& $this->c;
    $ct =& $this->ct;
    $nick   = $this->nick;
    $nick2  = $this->nick."2";
    $nickid = $this->nickid;
    $chan   = $this->chan;

    // create on the channel
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertTrue(in_array($nick, $online_nick), "1-nickname should be online on the channel");
    $ret = $this->ct->changeNick($chan, $nick2, $nick);
    $this->assertTrue($ret, "2-nickname change function should returns true (succes)");
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertFalse(in_array($nick, $online_nick), "3-nickname should not be online on the channel");
    $this->assertTrue(in_array($nick2, $online_nick), "4-nickname should be online on the channel");
    
    // create on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertTrue(in_array($nick, $online_nick), "5-nickname should be online");
    $ret = $this->ct->changeNick($chan, $nick2, $nick);
    $this->assertTrue($ret, "6-nickname change function should returns true (succes)");
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertFalse(in_array($nick, $online_nick), "7-nickname should not be online");
    $this->assertTrue(in_array($nick2, $online_nick), "8-nickname should be online on the channel");
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
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertTrue(in_array($nick, $online_nick), "1-nickname should be online on the channel");
    $msgid = $this->ct->write($chan, $nick, $cmd, $msg);
    $this->assertEquals($msgid, 1,"2- generated msg_id is not correct");
    $res = $this->ct->read($chan, 0);
    $this->assertEquals(1, count($res["data"]), "3- 1 messages should be read");
    $this->assertEquals($msg, $res["data"][0]["param"] ,"4- messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 1 ,"6- new_from_id is not correct");

    // create message on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan);   
    $this->assertTrue(in_array($nick, $online_nick), "7-nickname should be online on the channel");
    $msgid = $this->ct->write($chan, $nick, $cmd, $msg);
    $this->assertEquals($msgid, 1,"8- generated msg_id is not correct");
    $res = $this->ct->read($chan, 0);
    $this->assertEquals(1, count($res["data"]), "9- 1 messages should be read");
    $this->assertEquals($msg, $res["data"][0]["param"] ,"10- messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 1 ,"11- new_from_id is not correct");
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
    $online_nick = $this->ct->getOnlineNick($chan); 
    $this->assertTrue(in_array($nick, $online_nick), "1-nickname should be online on the channel");
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1,"2- generated msg_id is not correct");
    }

    $res = $this->ct->read($chan, 0);
    $this->assertEquals(10, count($res["data"]), "3- 10 messages should be read");
    $this->assertEquals($msg."0", $res["data"][0]["param"] ,"4- messages data is not the same as the sent one");
    $this->assertEquals($msg."9", $res["data"][9]["param"] ,"5- messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 10 ,"6- new_from_id is not correct");

    $res = $this->ct->read($chan, 5);
    $this->assertEquals(5, count($res["data"]), "7- 5 messages should be read");
    $this->assertEquals($msg."5", $res["data"][0]["param"] ,"8- messages data is not the same as the sent one");
    $this->assertEquals($msg."9", $res["data"][4]["param"] ,"9- messages data is not the same as the sent one");
    $this->assertEquals($res["new_from_id"], 10 ,"10- new_from_id is not correct");
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
    $online_nick = $this->ct->getOnlineNick($chan); 
    $this->assertTrue(in_array($nick, $online_nick), "1-nickname should be online on the channel");
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1,"2- generated msg_id is not correct");
    }
    $msgid = $this->ct->getLastId($chan);
    $this->assertEquals(10, $msgid, "3- last msgid is not correct");

    // on the server
    $chan = NULL;
    $this->ct->createNick($chan, $nick, $nickid);
    $online_nick = $this->ct->getOnlineNick($chan); 
    $this->assertTrue(in_array($nick, $online_nick), "4-nickname should be online on the channel");
    for($i = 0; $i < 10; $i++)
    {
      $msgid = $this->ct->write($chan, $nick, $cmd ,$msg . $i);
      $this->assertEquals($msgid, $i+1,"5- generated msg_id is not correct");
    }
    $msgid = $this->ct->getLastId($chan);
    $this->assertEquals(10, $msgid, "6- last msgid is not correct");
  }
}

?>
