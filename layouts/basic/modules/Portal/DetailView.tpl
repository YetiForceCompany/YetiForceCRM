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
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<div class="listViewPageDiv">
		<div class="btn-toolbar col-md-4">
			<span class="btn-group">
				<button id="addBookmark" class="btn btn-light addButton"><i class="fas fa-plus"></i>&nbsp;<strong>{\App\Language::translate('LBL_ADD_BOOKMARK', $MODULE)}</strong></button>
			</span>
		</div>
		<div class="col-md-2">&nbsp;</div>
		<div class="col-md-6">
			<div class="controls col-sm2 col-md-4 float-right">
				<select class="select2-container select2 float-right customFilterMainSpan" id="bookmarksDropdown" name="bookmarksList">
					{foreach item=RECORD from=$RECORDS_LIST}
						<option value="{$RECORD['id']}" {if $RECORD['id'] eq $RECORD_ID}selected{/if}>{$RECORD['portalname']}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-form-label col-md-8">
				<label class="textAlignRight float-right" style="padding-top: 14px;">
					{\App\Language::translate('LBL_BOOKMARKS_LIST', $MODULE)}
				</label>
			</div>
		</div>
		<span class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		<br />
		{if substr($URL, 0, 8) neq 'https://'}<div id="portalDetailViewHttpError" class="row"><div class="col-md-12">{\App\Language::translate('HTTP_ERROR', $MODULE)}</div></div>{/if}
		<br />
		<iframe src="{if substr($URL, 0, 4) neq 'http'}//{/if}{$URL}" frameborder="1" height="600" scrolling="auto" width="100%" style="border: solid 2px; border-color: #dddddd;"></iframe>
	</div>
{/strip}
