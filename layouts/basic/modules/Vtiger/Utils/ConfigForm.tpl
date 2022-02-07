{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Base-Utils-ConfigForm -->
	<table class="table table-bordered table-sm border-0">
		{if isset($CONFIG_HEAD_TITLE)}
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="mediumWidthType">
						{if isset($CONFIG_HEAD_ICON)}<span class="{$CONFIG_HEAD_ICON} mr-2"></span>{/if}
						{\App\Language::translate($CONFIG_HEAD_TITLE, $QUALIFIED_MODULE)}
					</th>
				</tr>
			</thead>
		{/if}
		<tbody>
			{foreach from=$CONFIG_FIELDS item=FIELD_MODEL}
				<tr class="row m-0">
					<td class="col-5 px-2">
						<label class="muted float-right text-right col-form-label u-text-small-bold">
							{\App\Language::translate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
							{if $FIELD_MODEL->get('labelDesc')}
								<span class="fas fa-info-circle float-right u-cursor-pointer text-primary ml-2 js-popover-tooltip" data-js="popover" data-content="
						{if $FIELD_MODEL->get('labelDescArgs')}
							{\App\Language::translateArgs($FIELD_MODEL->get('labelDesc'), $QUALIFIED_MODULE, $FIELD_MODEL->get('labelDescArgs'))}
						{else}
							{\App\Language::translate($FIELD_MODEL->get('labelDesc'), $QUALIFIED_MODULE)}
						{/if}" data-placement="top"></span>
							{/if}
						</label>
					</td>
					<td class="col-7 border-left-0">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<!-- /tpl-Base-Utils-ConfigForm -->
{/strip}
