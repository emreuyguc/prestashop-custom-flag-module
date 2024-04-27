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

namespace euu_customflag;

if (!defined('_PS_VERSION_'))
	exit;


use Module;
use HelperForm;
use Configuration;
use Tools;
use AdminController;
use euu_customflag\DbModels\CustomFlag;

trait ModuleConfigurationController {

	private $_config_form_success = [];
	private $_config_form_error= [];
	private $_config_form_warning= [];

	public function displayAjax() {
	}

	public function displayConfig() {
		if(Tools::getIsset('submitAddconfiguration')){
			$this->processSubmitAddconfiguration();
		}

		$config_prefix = $this->name . '_';
		$config_form = new HelperForm();
		$config_form_fields = [
			[
				'name' => 'configuration_custom_css',
				'type' => 'textarea',
				'label' => $this->l('Custom Css')
			]
		];
		$config_form->fields_value['configuration_custom_css'] = Configuration::get($config_prefix.'custom_css');

		return $config_form->generateForm(			array(
														  array(
															  'form' => array(
																  'legend' => array(
																	  'title' => $this->l('Settings'),
																  ),
																  'input' => $config_form_fields,
																  'submit' => [
																  	'title' => $this->l('Save'),
																	'class' => 'btn btn-default pull-right'
																  ],
																  'success' => implode("\n",$this->_config_form_success),
																  'error' => implode("\n",$this->_config_form_error),
																  'warning' => implode("\n",$this->_config_form_warning),
															  ),
														  ),
													  )
		);
	}

	public function processSubmitAddconfiguration(){
		foreach (Tools::getAllValues() as $key => $value) {
			if(explode('_',$key)[0] == 'configuration' ){
				$config_key = str_replace('configuration',$this->name,$key);
				$update = Configuration::updateValue($config_key,$value);
				if(!$update){
					$this->_config_form_error[] = $this->l($config_key.' config update error !');
				}
			}
		}
		if(!count($this->_config_form_error)){
			$this->clearCssCache();

			$this->_config_form_success[] = $this->l('Configuration update success.');
		}
	}

}