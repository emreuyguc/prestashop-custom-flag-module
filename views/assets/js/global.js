/**
 * @author emreuyguc <emreuyguc@gmail.com>
 * @copyright E.U.U 2022
 * @license Valid for 1 website (or project) and 1 domain only for each purchase of license
 * @package euu_customflag
 * @version 1.0.0
 *
 ** NOTICE OF LICENSE **
 *	This file is not open source ! Each license that you purchased is only available for 1 website (or project) and 1 domain only.
 *	If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * 	You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 ** DISCLAIMER **
 *	This SOFTWARE PRODUCT is provided by the PROVIDER "as is" and "with all defects".
 *	The PROVIDER makes no representations or warranties regarding the safety, suitability, absence of viruses, inaccuracies,
	typographical errors or other harmful components of this SOFTWARE PRODUCT.
 *	The use of any software has its own risks and you are solely responsible for determining whether this SOFTWARE PRODUCT is
	compatible with your system and other software installed on it.
 *	In addition, you are solely responsible for maintaining your system and backing up your data, and the PROVIDER will not be liable for
	any damage you may suffer in connection with use or modification.
 *
 **/
 
function ajaxProcess(request, success_callback, error_callback,then_callback) {
    $.ajax({
        async : request.async || true,
        url: currentIndex + (request.action ? '&action='+request.action : '') +'&token='+token,
        type: request.method || 'POST',
        data: request.data,
        dataType: "json",
        success: function (response) {
            if(request.showResponseMsg === true || request.showResponseMsg === 'success'){
                showNotification(response.code,response.msg);
            }
            success_callback(response);
        },
        error: function (ajax) {
            if(request.showResponseMsg === true || request.showResponseMsg === 'error'){
                if(ajax.responseJSON){
                    showNotification(ajax.responseJSON.code,ajax.responseJSON.msg);
                }
                else{
                    showNotification(ajax.status,ajax.statusText);
                }
            }
            if(error_callback){
                error_callback(ajax);
            }
        }
    }).then(function (data){
        if(then_callback){
            then_callback(data);
        }
    });
}


function confirm_action(head_text, display_text, confirm_text, cancel_text, confirm_callback, cancel_callback)
{
    $.alerts.okButton = confirm_text;
    $.alerts.cancelButton = cancel_text;
    jConfirm(display_text, head_text, function(confirm){
        if (confirm === true) {
            confirm_callback()
        }
        else{
            if(cancel_callback){
                cancel_callback()
            }
        }
    });

}