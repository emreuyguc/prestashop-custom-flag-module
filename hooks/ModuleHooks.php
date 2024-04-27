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

namespace euu_customflag\Hooks;

if (!defined('_PS_VERSION_')) exit;

use euu_customflag\DbModels\CustomFlag;
use euu_customflag\DbModels\GroupFlag;
use euu_customflag\DbModels\GroupProduct;
use Db;
use Tools;
use Context;
use euu_customflag\FlagBuilder;
use FlagGroup;

trait ModuleHooks {

	public function _afterInstall() {
		return CustomFlag::createTable() && FlagGroup::createTable() && GroupFlag::createTable() && GroupProduct::createTable();
	}

	public function _beforeInstall() {
		return TRUE;
	}

	public function _afterConstruct(){
		//todo chek tpl

		$this->_css_tpl = '/views/templates/front/hook/customflag-css.tpl';
		$this->_cache_id = $this->getCacheId($this->name.'|displayHeader');
	}

	public function hookActionProductFlagsModifier(&$params) {
		$flags = FlagBuilder::getInstance()->getProductFlags($params['product']['id_product']);

		foreach ($flags as $flag) {
			$params['flags']['euu_customflag-' . $flag['id_flag']] = [
				'type' => 'euu_customflag-' . $flag['id_flag'],
				'label' => empty($flag['img']) ? $flag['text'] : '',
				'icon' => [
					'name' => $flag['icon'],
					'color' => $flag['icon_color'],
					'size' => $flag['icon_size'],
				]
			];
		}
	}

	public function hookDisplayHeader() {
		if(!$this->isCached('module:'.$this->name.$this->_css_tpl,$this->_cache_id)){
			$this->smarty->assign([
									  'flags' => FlagBuilder::getInstance()->getFlags()
								  ]);
		}

		return $this->fetch('module:'.$this->name.$this->_css_tpl,$this->_cache_id);
	}

	public function clearCssCache(){
		$this->_clearCache($this->getLocalPath().$this->_css_tpl);
	}

}
	
