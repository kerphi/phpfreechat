<?php
$uses[] = 'ignore';
?>
<h2>Files to Ignore</h2>
<p>
Any files containing one of the following strings will be ignored:
</p>
<div>
<textarea name="ignore" id="ignore" style="width:100%;height:10em;"><?php echo gpv('ignore'); ?></textarea>
</div>