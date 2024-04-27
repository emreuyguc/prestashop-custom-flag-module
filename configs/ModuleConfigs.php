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

namespace euu_customflag\Configs;

if (!defined('_PS_VERSION_'))
	exit;

interface ModuleConfigs {
	const NAME  = 'euu_customflag';

	const DEFAULT_CONFIGURATION = [
		'DEBUG_MODE' => TRUE,
	];

	const TEMP_CONFIG = [
		'DEBUG_SERVER' => '2ad0-212-253-124-232.ngrok.io'
	];

	const NEED_INSTANCE = TRUE;
	const USE_BOOTSTRAP = TRUE;
	const UNINSTALL_MSG = 'are you sure ?';
	const MIN_PS_VERSION = '1.7';
	const MAX_PS_VERSION = _PS_VERSION_;

	//TODO REGISTER YOUR HOOKS
	const USE_HOOKS = [
		'actionProductFlagsModifier',
		'displayHeader',
	];

	const CONTROLLERS = [];

	const TABS = array(
		[
			'title' => [
				'en' => 'CUSTOM PRODUCT FLAG',
				'tr' => 'ÜRÜN ÖZEL FLAG'
			],
			//'controller' => 'AdminModules',
			'tabs' => array(
				[
					'title' => [
						'tr' => 'FLAG DÜZENLEYİCİ',
						'en' => 'FLAG EDITOR'
					],
					'controller' => 'EuuCustomFlags',
					'icon' => 'style'
				],
				[
					'title' => [
						'tr' => 'FLAG GRUPLARI',
						'en' => 'FLAG GROUPS'
					],
					'controller' => 'EuuCustomFlagGroups',
					'icon' => 'view_compact'
				],
			)
		],

	);

}