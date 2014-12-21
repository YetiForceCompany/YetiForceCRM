<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
?>
	</div>
	<?php if($GLOBALS["targetmodule"] != NULL){ ?>
	<footer class="noprint">
		<p>Copyright 2014 YetiForce.com All rights reserved. [ver. <?php echo $GLOBALS['version']; ?>]<br>Creating YetiForce software was possible thanks to the open source project called MYC Vtiger Customer Portal Theme and other programs that have open source codes.</p>
	</footer>
	<?php } ?>
    <!-- /#wrapper -->
</body>
<div class="modal fade" id="changePassModal" tabindex="-1" role="dialog" aria-labelledby="changePassModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo Language::translate("LBL_CLOSE"); ?></span></button>
        <h4 class="modal-title" id="changePassModalLabel"><?php echo Language::translate("LBL_CHANGING_PASSWORD"); ?></h4>
      </div>
      <div class="modal-body">
      <?php 
      $msgt="none";
      if(isset($GLOBALS["opresult"]) && $GLOBALS["opresult"]!="") 
      {
		if($GLOBALS["opresult"]=="LBL_PASSWORD_CHANGED") $msgt="success";
		else $msgt="warning";
		echo '<div class="alert alert-'.$msgt.'" role="alert">'.Language::translate($GLOBALS["opresult"]).'</div>'; 
      }
      ?>
      <?php if($msgt!="success"): ?>
        <form role="form" method="post">
		  <div class="form-group">
		    <label for="exampleInputPassword2"><?php echo Language::translate("LBL_OLD_PASSWORD"); ?></label>
		    <input type="password" class="form-control" name="old_password" id="exampleInputPassword2" placeholder="<?php echo Language::translate("LBL_OLD_PASSWORD"); ?>">
		  </div>
		  <div class="form-group">
		    <label for="exampleInputPassword1"><?php echo Language::translate("LBL_NEW_PASSWORD"); ?></label>
		    <input type="password" class="form-control" name="new_password" id="exampleInputPassword1" placeholder="<?php echo Language::translate("LBL_NEW_PASSWORD"); ?>">
		  </div>
		  <div class="form-group">
		    <label for="exampleInputPassword3"><?php echo Language::translate("LBL_CONFIRM_PASSWORD"); ?></label>
		    <input type="password" class="form-control" name="confirm_password" id="exampleInputPassword3" placeholder="<?php echo Language::translate("LBL_CONFIRM_PASSWORD"); ?>">
		  </div>
		<input type="hidden" name="fun" value="changepassword">
		<?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Language::translate("LBL_CLOSE"); ?></button>
        <?php if($msgt!="success"): ?><input type="submit" class="btn btn-primary" value="<?php echo Language::translate("LBL_BTN_CHANGE_PASSWORD"); ?>"><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
function getFileNameOnly(filename) {
	var onlyfilename = filename;
  	// Normalize the path (to make sure we use the same path separator)
 	var filename_normalized = filename.replace(/\\\\/g, '/');
  	if(filename_normalized.lastIndexOf("/") != -1) {
    	onlyfilename = filename_normalized.substring(filename_normalized.lastIndexOf("/") + 1);
  	}
  	return onlyfilename;
}
/* Function to validate the filename */
function validateFilename(form_ele) {
if (form_ele.value == '') return true;
	var value = form_ele.files[0].name;
	
	// Color highlighting logic
	var err_bg_color = "#FFAA22";
	if (typeof(form_ele.bgcolor) == "undefined") {
		form_ele.bgcolor = form_ele.style.backgroundColor;
	}
	// Validation starts here
	var valid = true;
	/* Filename length is constrained to 255 at database level */
	if (value.length > 255) {
		alert(alert_arr.LBL_FILENAME_LENGTH_EXCEED_ERR);
		valid = false;
	}
	if (!valid) {
		form_ele.style.backgroundColor = err_bg_color;
		return false;
	}
	form_ele.style.backgroundColor = form_ele.bgcolor;
	form_ele.form[form_ele.name + '_hidden'].value = value;
	return true;
}
$(function(){
	$(".chosen-select").chosen({disable_search_threshold: 10});
})
</script>
</html>