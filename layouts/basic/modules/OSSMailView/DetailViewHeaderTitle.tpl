{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="col-md-12 paddingLRZero row">
		<div class="col-xs-10 margin0px">
			<div class="moduleIcon">
				<span class="detailViewIcon userIcon-{$MODULE}"></span>
			</div>
			<div class="paddingLeft5px pull-left">
				<h4 style="color: #1560bd;" class="recordLabel textOverflowEllipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
				</h4>
				<span class="muted">
					<small><em>{\App\Language::translate('Sent','OSSMailView')}</em></small>
					<span><small><em>&nbsp;{$RECORD->getDisplayValue('createdtime')}</em></small></span>
				</span>
				<div>
					<strong>{\App\Language::translate('LBL_OWNER')} : {$RECORD->getDisplayValue('assigned_user_id')}</strong>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('DetailViewHeaderFields.tpl', $MODULE_NAME)}
	</div>
{/strip}
