<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
?>
<link class="include" rel="stylesheet" type="text/css" href="lib/jquery.jqplot/jquery.jqplot.min.css" />
<script type="text/javascript" src="lib/jquery.jqplot/jquery.jqplot.js"></script>
<script type="text/javascript" src="lib/jquery.jqplot/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="lib/jquery.jqplot/plugins/jqplot.donutRenderer.min.js"></script>
<script type="text/javascript" src="lib/morris/raphael.min.js"></script>
<script type="text/javascript" src="lib/morris/morris.js"></script>
<link class="include" rel="stylesheet" type="text/css" href="lib/morris/morris.css" />
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header"><?php echo Language::translate('LBL_HOME'); ?></h1>
		</div>
	</div>
	<?php
		foreach($data['widgets'] as $key => $data){
			$file = "modules/".$module."/layouts/".$key.".php";
			if(file_exists($file))
				require_once($file);
		}
	?>
</div>
