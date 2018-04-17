{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-md-12 pr-0 row">
		<div class="col-12 col-sm-12 col-md-8">
			<div class="moduleIcon">
				{if AppConfig::module($MODULE_NAME, 'COUNT_IN_HIERARCHY')}
					<span class="hierarchy"></span>
				{/if}
				<span class="detailViewIcon u-cursor-pointer userIcon-{$MODULE}"></span>
			</div>
			<div class="paddingLeft5px">
				<h4 class="recordLabel u-text-ellipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						&nbsp;&nbsp;
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</h4>
				{assign var=PARENTID value=$RECORD->get('parent_id')}
				{if !empty($PARENTID)}
					<div class="paddingLeft5px">
						<span class="muted">{\App\Language::translate('FL_PARENT',$MODULE_NAME)}: </span>
						{$RECORD->getDisplayValue('parent_id')}
					</div>
				{/if}
				<span class="muted">
					{\App\Language::translate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
					{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
					{if $SHOWNERS != ''}
						<br />{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
					{/if}
				</span>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME)}
	</div>
{/strip}
