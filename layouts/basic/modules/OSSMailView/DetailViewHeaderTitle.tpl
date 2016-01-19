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
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<input id="from_email" type="hidden" value="{$RECORD->get('from_email')}" />
	<input id="to_email" type="hidden" value="{$RECORD->get('to_email')}" />
	<input id="cc_email" type="hidden" value="{$RECORD->get('cc_email')}" />
	<input id="subject" type="hidden" value="{$RECORD->get('subject')}" />
	<input id="createdtime" type="hidden" value="{$RECORD->get('createdtime')}" />
	<div id="content" style="display: none;">{$RECORD->get('content')}</div>
	<div class="col-xs-10 col-sm-9 col-md-8 margin0px">
		<div class="moduleIcon">
			<span class="detailViewIcon userIcon-{$MODULE}"></span>
		</div>
		<div class="paddingLeft5px pull-left">
			<h4 style="color: #1560bd;" title="{$RECORD->getName()}">
				{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
					{if $FIELD_MODEL->getPermissions()}
						<span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
					{/if}
				{/foreach}
			</h4>
			<span class="muted">
				<small><em>{vtranslate('Sent','OSSMailView')}</em></small>
				<span><small><em>&nbsp;{$RECORD->get('createdtime')}</em></small></span>
			</span>
			<div>
				<strong>{vtranslate('LBL_OWNER','Emails')} : {getOwnerName($RECORD->get('assigned_user_id'))}</strong>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-sm-12 col-md-8 marginTopMinus10">
		{if $FIELDS_HEADER}
			{foreach from=$FIELDS_HEADER key=LABEL item=VALUE}
				<div class="col-md-12 marginTB3 paddingLRZero">
					<div class="row col-lg-6 col-md-6 pull-right paddingLRZero">
						<button class="btn  {$VALUE['class']} btn-xs col-md-12">
							<div class="col-md-6 text-left">
								{vtranslate($LABEL,$MODULE_NAME)} 
							</div>
							<div class="col-md-6 paddingLRZero">
								<span class="badge marginTB3 paddingLR10">
									{$VALUE['value']}
								</span>
							</div>
						</button>
					</div>
				</div>
			{/foreach}
		{/if}
	</div>
{/strip}
