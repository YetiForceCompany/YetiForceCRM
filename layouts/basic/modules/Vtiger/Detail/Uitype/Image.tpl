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
	{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
		{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
			<img class="tpl-Detail-Uitype-Image" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_INFO.path))}" width="150" height="80">
		{/if}
	{/foreach}
{/strip}
