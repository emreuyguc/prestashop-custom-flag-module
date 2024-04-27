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
	{assign var=custom_css value=Configuration::get('euu_customflag_custom_css')}
	{if !empty($custom_css)}
		{$custom_css}
	{/if}

	{foreach from=$flags item=flag}
		.euu_customflag-{$flag.id_flag}{
			{if !empty($flag.bg_color) && empty($flag.img)}background-color:{$flag.bg_color} !important;{/if}
			{if !empty($flag.text_color)}color:{$flag.text_color} !important;{/if}
			{if !empty($flag.text_size)}font-size:{$flag.text_size} !important;{/if}
			{if !empty($flag.text_style)}{$flag.text_style}{/if}
			{if !empty($flag.img)}background-color:unset !important;{literal}background-image:url({/literal}"{$flag.img_url}"{literal}) !important;{/literal} background-repeat: no-repeat !important;background-size: contain !important; width:{if !empty($flag.img_width)}{$flag.img_width}{else}100px{/if} !important; height:{if !empty($flag.img_height)}{$flag.img_height}{else}100px{/if} !important;{$flag.img_style}{/if}
		}

		{if !empty($flag.icon)}
			.euu_customflag-{$flag.id_flag} > i{
				{if !empty($flag.icon_color)}color:{$flag.icon_color} !important;{/if}
				{if !empty($flag.icon_size)}font-size:{$flag.icon_size} !important;{/if}
				{if !empty($flag.icon_style)}{$flag.icon_style}{/if}
			}
		{/if}


	{/foreach}
</style>