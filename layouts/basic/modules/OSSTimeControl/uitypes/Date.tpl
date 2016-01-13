{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{if $FIELD_MODEL->getName() == 'date_start' && in_array($VIEW, ['Edit', 'QuickCreateAjax']) }
		{assign var=MODULE_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_start')}
	{else if $FIELD_MODEL->getName() == 'due_date' && in_array($VIEW, ['Edit', 'QuickCreateAjax'])}
		{assign var=MODULE_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_end')}
	{/if}
	{if $TIME_FIELD}
		<div class="dateTimeField">
			<div class="col-xs-7 paddingLRZero">
				{include file=vtemplate_path('uitypes/Date.tpl','Vtiger') BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$FIELD_MODEL}
			</div>
			<div class="col-xs-5 paddingLRZero">
				{include file=vtemplate_path('uitypes/Time.tpl',$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$TIME_FIELD SKIP=true}
			</div>
		</div>
		{assign var=BLOCK_FIELDS value=false}
	{else}
		{include file=vtemplate_path('uitypes/Date.tpl','Vtiger') BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$FIELD_MODEL}
	{/if}
{/strip}
