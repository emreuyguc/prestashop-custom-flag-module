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

namespace euu_customflag\DbModels;

use euu_customflag\Helpers\DbModel;
use euu_customflag\Configs\ModuleConfigs;

if (!defined('_PS_VERSION_'))
	exit;

class GroupProduct extends DbModel {
	public $id;

	public $id_product;
	public $id_flag_group;

	#date
	public $date_add;
	public $date_upd;

	protected static $_USE_PREFIX = TRUE;
	public static $definition = [
		'table' => ModuleConfigs::NAME . '_group_products',
		'primary' => 'id_group_product',
		'fields' => [
			'id_product' => [
				'type' => self::TYPE_INT,
				'required' => TRUE
			],
			'id_flag_group' => [
				'type' => self::TYPE_INT,
				'required' => TRUE
			],
			// NOTE : USE INSERT
			'date_add' => [
				'type' => self::TYPE_DATE,
				'auto_date' => 'insert'
			],
			'date_upd' => [
				'type' => self::TYPE_DATE,
				'auto_date' => 'update'
			]
		],
	];

}