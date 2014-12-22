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
<div class="recordNamesList">
	<div class="row-fluid">
		<div class="">
			<ul class="nav nav-list">
				{foreach item=recordsModel from=$RECORDS}
				<li>
					<a data-id={$recordsModel->getId()} href="{$recordsModel->getDetailViewUrl()}" title="{decode_html($recordsModel->getName())}">{decode_html($recordsModel->getName())}</a>
				</li>
				{foreachelse}
					<li style="text-align:center">{vtranslate('LBL_NO_RECORDS', $MODULE)}
					</li>
				{/foreach}

			</ul>
		</div>
	</div>
</div>
{/strip}