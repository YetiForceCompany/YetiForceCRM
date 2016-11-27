{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}	
	<div class="col-md-12 paddingLRZero row">
		<div class="col-xs-12 col-sm-12 col-md-8">
			<div class="moduleIcon">
				<span class="hierarchy">

				</span>
				<span class="detailViewIcon cursorPointer userIcon-{$MODULE}" {if $COLORLISTHANDLERS}style="background-color: {$COLORLISTHANDLERS['background']};color: {$COLORLISTHANDLERS['text']};"{/if}></span>
			</div>
			<div class="paddingLeft5px">
				<h4 class="recordLabel textOverflowEllipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="moduleColor_{$MODULE_NAME}">{$RECORD->getName()}</span>
				</h4>
				<span class="muted">
					{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
					{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
					{if $SHOWNERS != ''}
						<br/>{vtranslate('Share with users',$MODULE_NAME)} {$SHOWNERS}
					{/if}
				</span>
			</div>
		</div>
		{include file='DetailViewHeaderFields.tpl'|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
