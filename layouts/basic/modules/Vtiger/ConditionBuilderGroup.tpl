{strip}
	<div class="tpl-Base-ConditionBuilderGroup js-condition-builder-group-container pl-4">
		<div class="btn-group btn-group-toggle js-condition-switch" data-toggle="buttons">
			<label class="btn btn-sm btn-outline-primary js-condition-switch-value">
				<input type="radio" autocomplete="off">
				AND
			</label>
			<label class="btn btn-sm btn-outline-primary active">
				<input type="radio" autocomplete="off">
				OR
			</label>
			<button class="btn btn-sm btn-success js-condition-add" data-js="click">
				<span class="fa fa-plus"></span>{\App\Language::translate('LBL_CONDITION',$MODULE_NAME)}
			</button>
			<button class="btn btn-sm btn-success js-group-add" data-js="click">
				<span class="fa fa-plus"></span>{\App\Language::translate('LBL_CONDITION_GROUP',$MODULE_NAME)}
			</button>
			<button class="btn btn-sm btn-danger js-group-delete" data-js="click">
				<span class="fa fa-trash"></span>
			</button>
		</div>
		<div class="js-condition-builder-conditions-container">

		</div>
	</div>
{/strip}