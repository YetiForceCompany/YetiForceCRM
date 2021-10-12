{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
    <!-- tpl-Base-SendSMSForm -->
    <div id="sendSmsContainer" class="js-send-sms__container modelContainer modal fade" tabindex="-1" data-js="hasClass">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{\App\Language::translate('LBL_SEND_SMS_TO_SELECTED_NUMBERS', $MODULE)}</h5>
                    <button type="button" class="close" data-dismiss="modal"
                        aria-label="{\App\Language::translate('LBL_CLOSE')}">
                        <span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
                    </button>
                </div>
                <form class="form-horizontal js-validate-form" id="massSave" method="post" action="index.php">
                    <input type="hidden" name="module" value="{$MODULE}" />
                    <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
                    <input type="hidden" name="action" value="MassSaveAjax" />
                    <input type="hidden" name="viewname" value="{$VIEWNAME}" />
                    <input type="hidden" name="selected_ids"
                        value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
                    <input type="hidden" name="excluded_ids"
                        value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
                    <input type="hidden" name="search_key" value="{$SEARCH_KEY}" />
                    <input type="hidden" name="entityState" value="{$ENTITY_STATE}" />
                    <input type="hidden" name="operator" value="{$OPERATOR}" />
                    <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
                    <input type="hidden" name="search_params"
                        value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <span class="fas fa-info-circle"></span>&nbsp;&nbsp;
                            {\App\Language::translate('LBL_MASS_SEND_SMS_INFO', $MODULE)}
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <span><strong>{\App\Language::translate('LBL_STEP_1',$MODULE)}</strong></span>
                                :&nbsp;
                                {\App\Language::translate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}
                                <select name="fields[]"
                                    data-placeholder="{\App\Language::translate('LBL_ADD_MORE_FIELDS',$MODULE)}"
                                    multiple="multiple" class="select2 form-control"
                                    data-validation-engine="validate[required]">
                                    <optgroup>
                                        {foreach item=PHONE_FIELD from=$PHONE_FIELDS}
                                            {if $PHONE_FIELD->isEditable() eq false} {continue} {/if}
                                            {assign var=PHONE_FIELD_NAME value=$PHONE_FIELD->get('name')}
                                            <option value="{$PHONE_FIELD_NAME}">
                                                {if !empty($SINGLE_RECORD)}
                                                    {assign var=FIELD_VALUE value=$SINGLE_RECORD->getDisplayValue($PHONE_FIELD_NAME)}
                                                {/if}
                                                {\App\Language::translate($PHONE_FIELD->get('label'), $SOURCE_MODULE)}{if !empty($FIELD_VALUE)}
                                                ({$FIELD_VALUE}){/if}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                </select>
                            </div>
                            <div class="form-group commentContainer">
                                <div>
                                    <span><strong>{\App\Language::translate('LBL_STEP_2',$MODULE)}</strong></span>
                                    :&nbsp;
                                    {\App\Language::translate('LBL_TYPE_THE_MESSAGE',$MODULE)}
                                    &nbsp;(&nbsp;{\App\Language::translate('LBL_SMS_MAX_CHARACTERS_ALLOWED',$MODULE)}&nbsp;)
                                </div>
                                <div class="js-add-comment-block addCommentBlock" data-js="container|remove">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend  js-completions__emojis">
                                            <button type="button" class="btn btn-outline-secondary">
                                                <span class=" far fa-smile"></span>
                                            </button>
                                        </div>
                                        <textarea name="message" class="u-min-height-70 form-control js-completions"
                                            data-validation-engine="validate[required]" maxlength="160"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SEND' BTN_DANGER='LBL_CANCEL'}
                </form>
            </div>
        </div>
    </div>
    <!-- /tpl-Base-SendSMSForm -->
{/strip}
