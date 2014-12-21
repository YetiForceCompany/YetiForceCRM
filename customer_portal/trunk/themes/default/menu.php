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
<!-- Navigation -->
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only"><?php echo Language::translate("LBL_TOGGLE_NAVIGATION"); ?></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a href="/" style="float: left;"><img alt="<?php echo Language::translate("LBL_NAVBAR_TITLE"); ?>" title="<?php echo Language::translate("LBL_NAVBAR_TITLE"); ?>" width="169" height="49" src="themes/default/images/logo.png"></a>
	</div>
	<!-- /.navbar-header -->
	<ul class="nav navbar-top-links navbar-right">
		<li><a href="index.php?logout=1"><span class="glyphicon glyphicon-log-out"></span> <?php echo Language::translate("LBL_BTN_LNK_LOGOUT"); ?></a></li>
		<li><a href="" data-toggle="modal" data-target="#changePassModal"><span class="glyphicon glyphicon-user"></span> <?php echo Language::translate("LBL_BTN_CHANGE_PASSWORD"); ?></a><li>
	</ul>
	<!-- /.navbar-top-links -->
	<div class="navbar-default sidebar" role="navigation">
		<div class="sidebar-nav navbar-collapse">
			<ul class="nav" id="side-menu"> 
			<?php if($GLOBALS['show_summary_tab']){ ?>
				<li><a href="index.php?module=Home&action=index"><?php echo Language::translate("LBL_DASHBOARD"); ?></a></li>
			<?php } ?>
			<?php foreach($GLOBALS["avmod"] as $mod){
				$active = '';
				if($mod['name'] == $GLOBALS['targetmodule'])
				$active = ' class="active"';
				if(!in_array($mod['name'], $GLOBALS['hiddenmodules']))
				echo '<li><a href="index.php?module='.$mod['name'].'&action=index"'.$active.'>'.$mod['translated_name'].'</a></li>';
			} ?>
			</ul>
		</div>
		<!-- /.sidebar-collapse -->
	</div>
	<!-- /.navbar-static-side -->
</nav>