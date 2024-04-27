{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'file_lang'}
        <div class="col-lg-9">
            {foreach from=$languages item=language}
            {if $languages|count > 1}
                <div class="translatable-field lang-{$language.id_lang}"
                     {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                    {/if}
                    <div class="form-group">
                        <div class="col-lg-6">
                            <input id="{$input.name}_{$language.id_lang}" type="file"
                                   name="{$input.name}_{$language.id_lang}" class="hide"/>
                            <div class="dummyfile input-group">
                                <span class="input-group-addon"><i class="icon-file"></i></span>
                                <input id="{$input.name}_{$language.id_lang}-name" type="text" class="disabled"
                                       name="filename" readonly/>
                                <span class="input-group-btn">
                                    <button id="{$input.name}_{$language.id_lang}-selectbutton" type="button"
                                            name="submitAddAttachments" class="btn btn-default">
                                        <i class="icon-folder-open"></i> {l s='Choose a file' }
                                    </button>

							    </span>
                            </div>
                        </div>
                        {if $languages|count > 1}
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1"
                                        data-toggle="dropdown">
                                    {$language.iso_code}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$languages item=lang}
                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang});"
                                               tabindex="-1">{$lang.name}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                    <div class="form-group">
                        {if isset($fields_value[$input.name][$language.id_lang]) && $fields_value[$input.name][$language.id_lang] != ''}
                            <div id="{$input.name}-{$language.id_lang}-images-thumbnails" class="col-lg-12">
                                <img src="/img/{$input.upload_folder}/{$fields_value[$input.name][$language.id_lang]}"
                                     width="{$input.thumb_size}px" class="img-thumbnail"/>
                                <a href="{$current}&deleteFile={$input.name}&fileLang={$language.id_lang}&token={$token}" id="{$input.name}_{$language.id_lang}-deletebutton">
                                    <i class="icon-trash"></i> {l s='Delete a file' }
                                </a>
                            </div>
                        {/if}
                    </div>
                    {if $languages|count > 1}
                </div>
            {/if}
                <script>
                    $(document).ready(function () {
                        $('#{$input.name}_{$language.id_lang}-selectbutton').click(function (e) {
                            $('#{$input.name}_{$language.id_lang}').trigger('click');
                        });
                        $('#{$input.name}_{$language.id_lang}').change(function (e) {
                            var val = $(this).val();
                            var file = val.split(/[\\/]/);
                            $('#{$input.name}_{$language.id_lang}-name').val(file[file.length - 1]);
                        });
                    });
                </script>
            {/foreach}
            {if isset($input.desc) && !empty($input.desc)}
                <p class="help-block">
                    {$input.desc}
                </p>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}