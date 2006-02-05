<?php
$uses[] = 'step_license';
?>
<div style="text-align:left;"><h1>Setup pages</h1></div>
<div><h2>Add/Remove pages</h2></div>
<div>
<label for="step_license">Include Licence page?</label>
<input type="checkbox" name="step_license" id="step_license" value="true"<?php
	if(gpv('step_license')){echo ' checked="checked"';} ?> /><br />
Please edit the <code>engene_data/license.html</code> file to include your licence.
</div>
