{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if isset($SELECTED_PAGE)}
				{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
			{/if}
		</div>
		<div class="col-md-4 marginbottomZero">
			<div class="pull-right btn-toolbar"><span class="actionImages">
					<button class="btn btn-info sendManually"
						<strong>{vtranslate('LBL_MANUAL_SENDING', $QUALIFIED_MODULE)}</strong>
					</button>
					{if $RECORD_MODEL->get('status') eq 0}
						<button class="btn btn-success acceptanceRecord">
							<strong>{vtranslate('LBL_ACCEPTANCE_RECORD', $QUALIFIED_MODULE)}</strong>
						</button>
					{/if}
					<a class="btn btn-danger" href="{$RECORD_MODEL->getDeleteActionUrl()}">
						<strong>{vtranslate('LBL_DELETE_RECORD', $QUALIFIED_MODULE)}</strong>
					</a>
			</div>

		</div>
	</div>
	<input type="hidden" value="{$RECORD_MODEL->getId()}" id="recordId">
	<div class="detailViewInfo" id="groupsDetailContainer">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}		
		<table class="table table-bordered">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_EMAIL_DETAIL',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_SMTP_NAME', $QUALIFIED_MODULE)} </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('smtp_id')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_DATE', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('date')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_CREATED_BY', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('owner')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_PRIORITY', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{vtranslate($RECORD_MODEL->getDisplayValue('priority'))}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">	{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('status')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">	{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('from')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_TO', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('to')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_CC', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('cc')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_BCC', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('bcc')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_SUBJECT', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('subject')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">	{vtranslate('LBL_ATTACHMENTS', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('attachments')}
					</td>
				</tr>
				<tr>
					<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{vtranslate('LBL_CONTENT', $QUALIFIED_MODULE)}  </label></td>
					<td class="{$WIDTHTYPE}">
						{$RECORD_MODEL->getDisplayValue('content')}
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	{strip}
