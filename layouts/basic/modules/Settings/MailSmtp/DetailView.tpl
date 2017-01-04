{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if isset($SELECTED_PAGE)}
				{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
	</div>
	<div class="detailViewInfo" id="groupsDetailContainer">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}		
		<table class="table table-bordered">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_SMTP_DETAIL',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_MAILER_TYPE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('mailer_type')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('name')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_HOST', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('host')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_PORT', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('port')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_USERNAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('username')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('password')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('authentication')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_SECURE', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('secure')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('from_email')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_FROM_NAME', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('from_name')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_REPLAY_TO', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('replay_to')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_OPTIONS', $QUALIFIED_MODULE)}</label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('from_name')}
					</td>
				</tr>

			</tbody>
		</table>
	</div>
	{strip}
