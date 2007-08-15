<?php require_once dirname(__FILE__)."/config.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>pfcSetup - Step 1 / 2</title>
  </head>
<body>
<h1>pfcSetup - Step 1 / 2</h1>
<?php if (!$isinstalled) { ?>
                           <table width="80%">
                           <tr>
                             <td>Archive to install</td><td><?php echo $archivename; ?></td>
                           </tr>
                           <tr>
                             <td>Install url</td><td><?php echo $dsturl; ?></td>
                           </tr>
                           <tr>
                             <td>Install path</td><td><?php echo $dstpath."/".$archivename2; ?></td>
                           </tr>
                           <tr>
                             <td>Archive will be downloaded from</td><td><?php echo $mirrors[0]."/".$archivename; ?></td>
                           </tr>
                           </table>

<?php if (!is_writable($dstpath)) { ?>
<p style="color:red;font-weight:bold;">./data directory is not writable. You must change the directory rights to 777 to continue the installation (ex: CHMOD 777 data)</p>
<?php } else {?>

<form action="step2.php" method="GET">
<?php if (isset($_GET["proxy"])) { ?>
  <input name="host" type="text" value="" />
  <input name="port" type="text" value="" />
<?php } ?>
  <input name="submit" type="submit" value="Installer" />  
</form>

 <?php }?>

<?php } else { ?>
<p>phpfreechat (<?php echo $archivename2; ?>) is already installed <a href="./data/<?php echo $archivename2; ?>">here</a>.</p>
<?php } ?>


  </body>
</html>