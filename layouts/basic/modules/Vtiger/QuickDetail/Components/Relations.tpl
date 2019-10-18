{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-QuickDetail-Components-Relations mt-3">
		{foreach item=MODULE from=$COMPONENT['modules']}
			{if \App\Privilege::isPermitted($MODULE['module'])}
				<div class="c-text-divider mb-3">
					<hr class="c-text-divider__line" />
					<span class="c-text-divider__title bg-white">{App\Language::translate($MODULE['module'], $MODULE['module'])}</span>
				</div>
				{assign var=RELATION value=$MODAL_VIEW->getRelationRecords($MODULE)}
					<table class="table table-bordered listViewEntriesTable">
						<thead>
							<tr class="listViewHeaders">
								{foreach item=FIELD_MODEL from=$RELATION['headers']}
									<td class="{$WIDTHTYPE}">{App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE['module'])}</th>
								{/foreach}
							</tr>
						</thead>
						{foreach item=RELATED_RECORD key=ID from=$RELATION['entries']}
							<tr data-id="{$ID}">
								{foreach item=FIELD_MODEL key=NAME from=$RELATION['headers']}
									<td class="{$WIDTHTYPE}" >
										{$RELATED_RECORD->getListViewDisplayValue($NAME)}
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</table>
				{/if}
		{/foreach}
{/strip}
