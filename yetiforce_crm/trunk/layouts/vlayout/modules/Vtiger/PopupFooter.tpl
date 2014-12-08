{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
	{* javascript files *}
	{include file='JSResources.tpl'|@vtemplate_path}
	</body>
</html>
{/strip}
<script type=text/javascript>
	jQuery(document).ready(function() {
		jQuery.triggerParentEvent('Vtiger.OnPopupWindowLoad.Event');
	});
</script>