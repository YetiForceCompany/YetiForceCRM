{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<li class="newCustomFieldCopy d-none">
		<div class="opacity js-custom-field ml-0 border1px" data-block-id="" data-field-id="" data-sequence="">
			<div class="px-2 py-1">
				<div class="col-12 pr-0 fieldContainer" style="word-wrap: break-word;">
					{if $IS_SORTABLE}
						<a class="mr-3">
							<img src="{\App\Layout::getImagePath('drag.png')}" border="0" alt="{App\Language::translate('LBL_DRAG', $QUALIFIED_MODULE)}"/>
						</a>
					{/if}
					<span class="fieldLabel"></span>
					<span class="float-right actions">
						<input type="hidden" value="" id="relatedFieldValue" />
						{if $IS_SORTABLE}
							<button class="btn btn-success btn-xs editFieldDetails ml-1">
								<span class="yfi yfi-full-editing-view" title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
							</button>
						{/if}
						<button class="btn btn-primary btn-xs copyFieldLabel ml-1" data-target="relatedFieldValue">
							<span class="fas fa-copy" title="{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
						</button>
						<button class="btn btn-danger btn-xs deleteCustomField ml-1" data-field-id="">
							<span class="fas fa-trash-alt" title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
						</button>
					</span>
				</div>
			</div>
		</div>
	</li>
{/strip}
