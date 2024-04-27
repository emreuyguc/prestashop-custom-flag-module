<?php
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

namespace euu_customflag\Utils;

use euu_customflag\Configs\ModuleConfigs;

if (!defined('_PS_VERSION_')) exit;

trait DebugUtility {

	public function consoleLog($data) {
		if (ModuleConfigs::DEFAULT_CONFIGURATION['DEBUG_MODE'] == TRUE) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, ModuleConfigs::TEMP_CONFIG['DEBUG_SERVER']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, print_r($data, TRUE));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_exec($ch);
			curl_close($ch);
		}
	}

}