{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
<div class="relatedContainer">
	<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
	<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
	<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
	<div class="relatedHeader ">
		<div class="btn-toolbar row-fluid">
			<div class="span8">
				{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
					<div class="btn-group">
						{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
						<button type="button" class="btn addButton
							{if $IS_SELECT_BUTTON eq true} selectRelation {/if} moduleColor_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
							{if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
							{if ($RELATED_LINK->isPageLoadLink())}
                                    {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                                    data-url="{$RELATED_LINK->getUrl()}"
                            {/if}
						{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
					</div>
				{/foreach}
				&nbsp;
			</div>
			<div class="span4">
				<span class="row-fluid">
					<span class="span7 pushDown">
						<span class="pull-right">
						{if !empty($RELATED_RECORDS)} {$PAGING->getRecordStartRange()} {vtranslate('LBL_TO', $RELATED_MODULE->get('name'))} {$PAGING->getRecordEndRange()}{if $TOTAL_ENTRIES} {vtranslate('LBL_OF', $RELATED_MODULE->get('name'))} {$TOTAL_ENTRIES}{/if}{/if}
						</span>
					</span>
					<span class="span5 pull-right">
						<span class="btn-group pull-right">
							<button class="btn" id="relatedListPreviousPageButton" {if !$PAGING->isPrevPageExists()} disabled {/if} type="button"><span class="icon-chevron-left"></span></button>
							<button class="btn dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
								<span><img src="{vimage_path('ListViewJump.png')}" alt="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}" /></span>
							</button>
							<ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown">
								<li>
									<span class="row-fluid">
										<span class="span3"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
										<span class="span4">
											<input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGING->getCurrentPage()}"/>
										</span>
										<span class="span2 textAlignCenter">
											{vtranslate('LBL_OF',$moduleName)}
										</span>
										<span class="span2" id="totalPageCount"></span>
									</span>
								</li>
							</ul>
							<button class="btn" id="relatedListNextPageButton" {if !$PAGING->isNextPageExists()} disabled {/if} type="button"><span class="icon-chevron-right"></span></button>
						</span>
					</span>
				</span>
			</div>
		</div>
	</div>
	<div class="contents-topscroll">
		<div class="topscroll-div">
		&nbsp;
		</div>
	</div>
	<div class="relatedContents contents-bottomscroll">
		<div class="bottomscroll-div">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
								{if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
									<a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
								{elseif $HEADER_FIELD->get('column') eq 'time_start'}
								{else}
									<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
										&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE} icon-white">{/if}
									</a>
								{/if}
							</th>
						{/foreach}
					</tr>
				</thead>
				{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                    {$PASS_ID=''}
					<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
                            {* create id for posword *}
                            {if $RELATED_HEADERNAME eq 'password'} 
                                {$PASS_ID="{$RELATED_RECORD->get('id')}"}
                            {/if}
							<td data-field-type="{$HEADER_FIELD->getFieldDataType()}" {if $RELATED_HEADERNAME eq 'password'} id="{$PASS_ID}" {/if} nowrap>
								{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
									<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                                {elseif $RELATED_HEADERNAME eq 'password'}
                                    {str_repeat('*', 10)}
								{elseif $RELATED_HEADERNAME eq 'access_count'}
									{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
								{elseif $RELATED_HEADERNAME eq 'time_start'}
								{else}
									{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
								{/if}
							{if $HEADER_FIELD@last}
								</td><td nowrap>
							<div class="pull-right actions"> 
                               <span class="actionImages">
                                <a href='' class="show_pass" id="btn_{$PASS_ID}"><i title="{vtranslate('LBL_ShowPassword', $SOURCEMODULE)}" class="icon-eye-open alignMiddle"></i></a>&nbsp;
									<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
									{if $IS_EDITABLE}
										<a href='{$RELATED_RECORD->getEditViewUrl()}'><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></i></a>
									{/if}
									{if $IS_DELETABLE}
										<a class="relationDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
									{/if}
								</span>
							</div>
                            {* button for copying password to clipboard *}                    
                            <div class="pull-right">
                                <a href='' id="copybtn_{$PASS_ID}" data-clipboard-target="{$PASS_ID}" class="copy_pass hide" title="{vtranslate('LBL_CopyToClipboardTitle', $SOURCEMODULE)}" ><i class="icon-download-alt alignMiddle"></i></a>&nbsp;
                            </div>
								</td>
							{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>

{* translation strings for javascript functions, because js takes translation from related module not from source module *}
{$TRANS_STRINGS="{vtranslate('LBL_ShowPassword', $SOURCEMODULE)},{vtranslate('LBL_HidePassword', $SOURCEMODULE)}"}

{$TOOLTIP_TITLE="{vtranslate('LBL_NotifPassTitle', $SOURCEMODULE)}"}
{$TOOLTIP_TEXT="{vtranslate('LBL_NotifPassCopied', $SOURCEMODULE)}"}

{literal}
<script type="text/javascript" src="libraries/jquery/ZeroClipboard/ZeroClipboard.js"></script>
<script>
if ( typeof zaladowany !== 'undefined' && zaladowany == 1 ) {
    // jeśli ta zmienna istnieje tzn. że kliknięto przycisk cofnij w przeglądarce
    // przeładuj stronę
    location.reload();
}
    
var zaladowany = 1;     // po tej zmiennej sprawdzamy czy skrypt jest już załadowany w pamięci

$(document).ready( function() {
    // show/hide password
    $('.show_pass').click( function(e) { // rejestruj zdarzenie click
        var id = $(this).attr('id').substr(4); 
        showPassword( id, '{/literal}{$TRANS_STRINGS}{literal}' );
        
        return false;
    });

    
    // copy password to clipboard
    var clip2 = new ZeroClipboard( 
        $('[id^=copybtn_]'), {
        moviePath: "libraries/jquery/ZeroClipboard/ZeroClipboard.swf"
    });
    
    clip2.on( 'complete', function(client, args) {
        // notification about copy to clipboard
        var params = {
            text: "{/literal}{$TOOLTIP_TEXT}{literal}",
            animation: 'show',
            title: "{/literal}{$TOOLTIP_TITLE}{literal}",
            type: 'success'
        };
        Vtiger_Helper_Js.showPnotify(params);
    } );
    
    
    // function that shows or hides password
    function showPassword( record, translation ) {
        var passVal = $('#'+record).html(); // current value of password
        // button labels
        if ( translation.length > 0 ) {
            var tstrings = translation.split( ',' );
            var showPassText = tstrings[0];
            var hidePassText = tstrings[1];
        }
        else {
            var showPassText = app.vtranslate('LBL_ShowPassword');
            var hidePassText = app.vtranslate('LBL_HidePassword');
        }
        
        // if password is hashed, show it
        if ( passVal == '**********' ) {
            var params = {
                'module' : "OSSPasswords",
                'action' : "GetPass",
                'record' : record
            }
            
            AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if (response['success']) {
                        // show password
                        $('#'+record).html( response['password'] );
                        // change button title to 'Hide Password'
                        $('a#btn_'+record+' i').attr( 'title', hidePassText );
                        // change icon
                        $('a#btn_'+record+' i').removeClass( 'icon-eye-open' );
                        $('a#btn_'+record+' i').addClass( 'icon-eye-close' );
                        // show copy to clipboard button
                        $('a#copybtn_'+record).toggleClass('hide');
                    }
                },
                function(data,err){
                
                }
            );
        }
        // if password is not hashed, hide it
        else {
            // hide password
            $('#'+record).html( '**********' );
            // change button title to 'Show Password'
            $('a#btn_'+record+' i').attr( 'title', showPassText );
            // change icon
            $('a#btn_'+record+' i').removeClass( 'icon-eye-close' );
            $('a#btn_'+record+' i').addClass( 'icon-eye-open' );
            // hide copy to clipboard button
            $('a#copybtn_'+record).toggleClass('hide');
        }
    }
});
</script>
{/literal}
{/strip}
