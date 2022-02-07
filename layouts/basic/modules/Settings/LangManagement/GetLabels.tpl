{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 id="myModalLabel" class="modal-title">{\App\Language::translate('LBL_UNTRANSLATED_LABELS',$QUALIFIED_MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body form-horizontal">
		<div class="table-responsive">
			<table class="table table-bordered table-sm tableMiddle">
				{foreach from=$DATA item=TRANSLATIONS_BY_TYPE key=TYPE name=dataType}
					{foreach from=$TRANSLATIONS_BY_TYPE item=TRANSLATIONS key=LABEL name=data}
						{if $smarty.foreach.dataType.first && $smarty.foreach.data.first}
							<thead>
								<tr class="blockHeader">
									<th><strong>{\App\Language::translate('LBL_variable',$QUALIFIED_MODULE)}</strong></th>
									{foreach from=$TRANSLATIONS item=VALUE key=LANG_KEY name=header}
										<th><strong>{$LANG_KEY}</strong></th>
									{/foreach}
									<th><strong>{\App\Language::translate('LBL_ACTIONS',$QUALIFIED_MODULE)}</strong></th>
								</tr>
							</thead>
							<tbody>
							{/if}
							<tr data-langkey="{$LABEL}">
								<td>{$LABEL}</td>
								{foreach from=$TRANSLATIONS item=VALUE key=LANG_KEY}
									{if $LANG eq $LANG_KEY}
										<td class="col-4">
											<input data-lang="{$LANG}"
												data-type="{$TYPE}"
												name="{$LABEL}"
												class="form-control {if $VALUE == NULL}empty_value{/if}"
												type="text"
												data-mod="{$SOURCE_MODULE}"
												value="{$VALUE}">
										</td>
										<td>
											<button type="button" class="btn btn-success" title="{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}">
												<span class="fas fa-check"></span>
											</button>
										</td>
									{else}
										<td>{$VALUE}</td>
									{/if}
								{/foreach}
							</tr>
						{/foreach}
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
