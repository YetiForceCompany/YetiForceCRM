{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	<div class="" id="AnnouncementContainer">
		<div class="widget_header">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
		<hr>
		<div class="contents">
			<textarea class="announcementContent textarea-autosize boxSizingBorderBox form-control" rows="3" placeholder="{vtranslate('LBL_ENTER_ANNOUNCEMENT_HERE', $QUALIFIED_MODULE)}" style="width:100%">{$ANNOUNCEMENT->get('announcement')}</textarea>
			<div class="textAlignCenter">
				<br>
				<button class="btn btn-success saveAnnouncement pull-right hide"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
			</div>
		</div>
	</div>
{/strip}
