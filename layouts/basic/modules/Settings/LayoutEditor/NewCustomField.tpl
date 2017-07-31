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
	<li class="newCustomFieldCopy hide">
		<div class="marginLeftZero border1px" data-field-id="" data-sequence="">
			<div class="row padding1per">
				<span class="col-md-2">&nbsp;
					{if $IS_SORTABLE}
						<a>
							<img src="{vimage_path('drag.png')}" border="0" alt="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
						</a>
					{/if}
				</span>
				<div class="col-md-10 marginLeftZero fieldContainer">
					<span class="fieldLabel"></span>
					<input type="hidden" value="" id="relatedFieldValue" />
					<span class="pull-right actions">
						<button class="btn btn-primary btn-xs copyFieldLabel pull-right marginLeft5" data-target="relatedFieldValue">
							<span class="glyphicon glyphicon-copy" title="{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
						</button>
						{if $IS_SORTABLE}
							<button class="btn btn-success btn-xs editFieldDetails marginLeft5">
								<span class="glyphicon glyphicon-pencil" title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
							</button>
						{/if}
						<button type="button" class="btn btn-danger btn-xs deleteCustomField marginLeft5" data-field-id="">
							<span class="glyphicon glyphicon-trash" title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
						</button>
					</span>
				</div>
			</div>
		</div>
	</li>
{/strip}