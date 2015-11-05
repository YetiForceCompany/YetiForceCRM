/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

Vtiger_Edit_Js("OSSPdf_Edit_Js",{},{
    /**
     * Registers updated version of CkEditor on textarea fields
     * spellcheck disabled
     */
    registerNewCkEditor : function( form ) {
        CKEDITOR.replace('header_content', {
            disableNativeSpellChecker: true,
            scayt_autoStartup: false,
            removePlugins: 'scayt'}
        );
        
        CKEDITOR.replace('content', {
            disableNativeSpellChecker: true,
            scayt_autoStartup: false,
            removePlugins: 'scayt',
            height: 1000}
        );
        
        CKEDITOR.replace('footer_content', {
            disableNativeSpellChecker: true,
            scayt_autoStartup: false,
            removePlugins: 'scayt'}
        );
    },
    editFormContents: function(editViewForm){
		editViewForm.find('#addFieldName').on('change',function(){
            var fieldName = $('#OSSPdf_editView_fieldName_filename').val();
            $('#OSSPdf_editView_fieldName_filename').val(fieldName+jQuery(this).val());
        });
		editViewForm.find('.btn input[type="checkbox"]').on('change',function(){
			element = jQuery(this).closest('label');
			element.toggleClass('active');
			blockId = 'DOC_'+element.data('block');
			jQuery('#'+blockId).toggleClass('hide');
		});
	},
    /**
     * Register events
     */
	registerEvents: function(){
		this._super();
		var editViewForm = this.getForm();
        this.editFormContents( editViewForm );
        this.registerNewCkEditor( editViewForm );
	}
});
