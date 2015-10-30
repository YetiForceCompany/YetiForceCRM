{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
{strip}
	<div class="pull-right actions">
		<span class="actionImages">
			{if $RELATED_MODULE_NAME eq 'Calendar'}
				{assign var=CURRENT_ACTIVITY_LABELS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
				{if $IS_EDITABLE && in_array($RELATED_RECORD->get('activitystatus'),$CURRENT_ACTIVITY_LABELS)}
					<a class="showModal" data-url="{$RELATED_RECORD->getActivityStateModalUrl()}"><span title="{vtranslate('LBL_SET_RECORD_STATUS', $MODULE)}" class="glyphicon glyphicon-ok alignMiddle"></span></a>&nbsp;
				{/if}
				{if $DETAILVIEWPERMITTED eq 'yes'}
				<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
				{/if}
			{else}
				<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
			{/if}
			{if $IS_EDITABLE}
				{if $RELATED_MODULE_NAME eq 'PriceBooks'}
				<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
				   class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
					<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
				</a>
			{elseif $RELATED_MODULE_NAME eq 'Calendar'}
				{if isPermitted($RELATED_MODULE->get('name'), 'EditView', $RELATED_RECORD->getId()) eq 'yes'}
					<a href='{$RELATED_RECORD->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>
					{/if}
				{else}
				<a href='{$RELATED_RECORD->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>
				{/if}
			{/if}
			{if $IS_DELETABLE}
				{if $RELATED_MODULE_NAME eq 'Calendar'}
					{if isPermitted($RELATED_MODULE->get('name'), 'Delete', $RELATED_RECORD->getId()) eq 'yes'}
					<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
					{/if}
				{else}
				<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
				{/if}
			{/if}
		</span>
	</div>
{/strip}

