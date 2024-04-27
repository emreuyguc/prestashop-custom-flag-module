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
<style>
    .selectize-input {
        min-width: 500px !important;
    }
</style>
<div id="flagGroupQueryBuilder"></div>

<script>
    $('document').ready(function () {
        const flagGroupQueryBuilder = $('#flagGroupQueryBuilder').queryBuilder({
            sort_filters: true,
            allow_empty: true,
            filters: [
                {foreach from=$filters item=filter}
                {
                    id: '{$filter.field}',
                    label: '{$filter.label}',
                    type: '{$filter.type}',
                    {if isset($filter.input)}input: '{$filter.input}',{/if}
                    {if isset($filter.multiple)}multiple: '{$filter.multiple}',{/if}
                    {if isset($filter.validation)}validation: {$filter.validation},{/if}
                    {if isset($filter.operators)}operators: ['{'\',\''|implode:$filter.operators}'],{/if}
                    {if isset($filter.values)}values: {$filter.values},{/if}
                    {if isset($filter.plugin)}plugin: '{$filter.plugin}',{/if}
                    {if isset($filter.plugin_config)}plugin_config: {

                        {*
                        render: {
                            option: function (item, escape) {
                                return '<div >' + escape(item.name) + '</div>';
                            }
                        },
                        *}
                        {if isset($filter.plugin_config.ajax_action)}
                        load: function (query, callback) {
                            if (!query.length) return callback();
                            ajaxProcess({
                                data: {
                                    search: query
                                },
                                action: '{$filter.plugin_config.ajax_action}',
                            }, function (response) {
                                callback(response);
                            })
                        },
                        {/if}
                        {if $filter.plugin == 'selectize'}
                        valueSetter: function (rule, value) {
                            rule.$el.find('.rule-value-container input')[0].selectize.setValue(value);
                        },{/if}
                        {foreach from=$filter.plugin_config key=k  item=plugin}
                        {if !($k|in_array:['ajax_action'])}
                            {$k} : {if is_array($plugin)} {json_encode($plugin)} {else} '{$plugin}' {/if},
                        {/if}
                        {/foreach}
                        {*
                         {if isset($filter.plugin_config.value_field)} valueField: '{$filter.plugin_config.value_field}' ,{/if}
                         {if isset($filter.plugin_config.label_field)} labelField: '{$filter.plugin_config.label_field}' ,{/if}
                         {if isset($filter.plugin_config.search_field)} searchField: '{$filter.plugin_config.search_field}' ,{/if}
                         {if isset($filter.plugin_config.sort_field)}  sortField: '{$filter.plugin_config.sort_field}' ,{/if}
                         {if isset($filter.plugin_config.create)} create: {$filter.plugin_config.create},{/if}
                         {if isset($filter.plugin_config.max_items)} maxItems: {$filter.plugin_config.max_items},{/if}
                         {if isset($filter.plugin_config.plugins)} plugins: {$filter.plugin_config.plugins},{/if}
                         {if isset($filter.plugin_config.format)} format: {$filter.plugin_config.format},{/if}

                        *}
                    }
                    {/if}
                },
                {/foreach}
            ]
        });


        if ($('textarea[name="sql"]').val().length > 0) {
            flagGroupQueryBuilder.queryBuilder('setRulesFromSQL', $('textarea[name="sql"]').val());
        }


        $('#formQuery').submit(function (e) {
            e.preventDefault();

            let form = $(this);
            let form_data = {};
            form.serializeArray().map(function (item) {
                form_data[item.name] = item.value
            })

            form_data.id_flag_group = $('input[name="id_flag_group"]').val();
            form_data.query = flagGroupQueryBuilder.queryBuilder('getSQL').sql;

            if (form_data.query === undefined) {
                return false;
            }
            form_data.action = form.find('button[type="submit"]').val();
            ajaxProcess({
                async: false,
                data: form_data
            }, function (response) {
                if (response.status == 'ok') {
                    if (response.content.affected_row_count > 0) {
                        bootbox.confirm({
                            message: response.confirmations.join('<br>'),
                            buttons: {
                                cancel: {
                                    label: '<i class="fa fa-times"></i> {l s="Cancel" js=1}'
                                },
                                confirm: {
                                    label: '<i class="fa fa-check"></i> {l s="Confirm" js=1}'
                                }
                            },
                            callback: function (result) {
                                if (result) {
                                    $('#modalRunQuery').modal('show');
                                    form_data.action = 'runQuery';
                                    ajaxProcess({
                                        async: false,
                                        data: form_data
                                    }, function (response) {
                                        if (response.status == 'ok') {
                                            $('textarea[name="sql"]').val(flagGroupQueryBuilder.queryBuilder('getSQL').sql)
                                            $('#resultRunQuery').html('{l s="Query run SUCCESS." js=1}')
                                        } else {
                                            $('#resultRunQuery').html('{l s="The query could not be run. The response returned an ERROR !" js=1}')
                                        }
                                    })
                                }
                            }
                        });
                    } else {
                        bootbox.alert({
                            message: '{l s="No rows found from the result of your query" js=1}',
                            size: 'small'
                        });
                    }
                } else {
                    bootbox.alert({
                        message: response.error.join('<br>') ?? '{l s="Query Result Error !" js=1}',
                        size: 'small'
                    });
                }
            })
        })
    })

</script>