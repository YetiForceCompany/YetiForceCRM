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
	<div id="TagCloudResults" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
					<div class="row">
						<h3 class="col-md-8 modal-title">{vtranslate('LBL_RESULT_FOR_THE_TAG', $MODULE)} - {$TAG_NAME}</h3>
						{if $TAGGED_RECORDS}
							<div class="col-md-3">
								<select id="tagSearchModulesList" class="chzn-select form-control">
								<option value="all">{vtranslate('LBL_ALL',$MODULE)}</option>
								{foreach key=MODULE_NAME item=TAGGED_RECORD_MODELS from=$TAGGED_RECORDS}
									<option value="tagSearch_{$MODULE_NAME}">{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
								{/foreach}	
							</select>
							</div>
							
						{/if}
					</div>
				</div>
				<div class="modal-body tabbable">
					{if $TAGGED_RECORDS}
						{foreach key=MODULE_NAME item=TAGGED_RECORD_MODELS from=$TAGGED_RECORDS}
							<div name="tagSearchModuleResults" id="tagSearch_{$MODULE_NAME}">
								<h5>{vtranslate($MODULE_NAME,$MODULE_NAME)} ({count($TAGGED_RECORD_MODELS)})</h5>
								{foreach item=TAGGED_RECORD_MODEL from=$TAGGED_RECORD_MODELS}
									<div><a href="{$TAGGED_RECORD_MODEL->getDetailViewUrl()}">{$TAGGED_RECORD_MODEL->getName()} </a></div>
								{/foreach}
								<br>
							</div>
						{/foreach}
					{else}
						<div class="alert alert-block"><strong>{vtranslate('LBL_NO_RECORDS_FOUND',$MODULE)}.</strong></div>
					{/if}	
				</div>
			</div>
		</div>
    </div>
{/strip}	
