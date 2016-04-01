{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="media noticeRow" data-id="{$ROW->getId()}" data-type="{$ROW->get('type')}">
		{assign var=ICON value=$ROW->getIcon()}
		{if $ICON}
			<div class="media-left media-middle">
				{if $ICON['type'] == 'image'}
					<img width="30px" class="{$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" src="{$ICON['src']}"/>
				{else}
					<span class="{$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" aria-hidden="true"></span>
				{/if}
			</div>
		{/if}
		<div class="media-body media-middle wordBreakAll">
			<div class="pull-right">
				<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($ROW->get('time'))}">
					{Vtiger_Util_Helper::formatDateDiffInStrings($ROW->get('time'))}
				</small>
			</div>
			<strong>{$ROW->getTitle()}</strong>
			{if $SHOW_TYPE}
				&nbsp;({vtranslate($ROW->getTypeName(), $MODULE_NAME)})
			{/if}
			<br/>
			{$ROW->getMassage()}
		</div>
		<div class="media-right media-middle">
			{foreach from=$ROW->getActions() item=ACTION}
				<button class="btn {$ACTION['class']}" {if $ACTION['action']}onclick="{$ACTION['action']}"{/if} type="button">
					{if $ACTION['name']}
						{vtranslate($ACTION['name'], $MODULE_NAME)}
					{/if}
					{if $ACTION['icon']}
						<span class="{$ACTION['icon']}" title="{vtranslate($ACTION['title'], $MODULE_NAME)}" aria-hidden="true"></span>
					{/if}
				</button>
			{/foreach}
		</div>
	</div>
{/strip}
