{*
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
*}
<form method="post">
	<div class="modal-body defaultForm form-horizontal">
		<input type="hidden" id="input-modal-id-flag-group" name="id_flag_group" >
		<div class="form-group">
			<label class="control-label col-lg-4 required">
				{l s="Flag"}
			</label>
			<div class="col-lg-8">
				<select id="select-modal-id-flag" name="id_flag" class="fixed-width-xxl">
					<option value="" selected disabled>{l s="--Choose--"}</option>
                    {foreach from=$flags item=flag}
						<option value="{$flag.id_flag}">{$flag.name}</option>
                    {/foreach}
				</select>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary" id="btn-save-carousel-seller" name="action" value="saveGroupFlag">{l s="Save"}</button>
		<button type="button" class="btn btn-secondary" data-dismiss="modal">{l s="Close"}</button>
	</div>
</form>
<script>
    $('#group-flag-modal').on('show.bs.modal', function () {
        $('#input-modal-id-flag-group').val($('input[name="id_flag_group"]').val())
        $('#select-modal-id-flag').val('')
    });


    $('#group-flag-modal').on('hidden.bs.modal', function () {
        $('#input-modal-id-flag-group').val('')
        $('#select-modal-id-flag').val('')
    });

    function deleteGroupFlag(id_group_flag){
        ajaxProcess({
            action: "deleteRow",
            data: {
                'group_flag': id_group_flag
            }
        }, function(response) {
            if (response.status == "ok") {
                let data = response.content;
                $.growl.notice({
                    title: '{l s="Success" js=1}',
                    size: "large",
                    message: '{l s="Row delete success" js=1}'
                });

                $("#tr_2_" + data.id + "_" + data.position).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                $.growl.error({
                    title: '{l s="Fail" js=1}',
                    size: "large",
                    message: '{l s="Response Fail" js=1}'
                });
            }
        }, function() {
            $.growl.error({
                title: '{l s="Fail" js=1}',
                size: "large",
                message: '{l s="Request Fail , Controller Error" js=1}'
            });
        })
    }
</script>