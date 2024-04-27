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

if (!defined('_PS_VERSION_'))
	exit;

use euu_customflag\Helpers\DbModelList;
use euu_customflag\Helpers\DbModelForm;

use euu_customflag\DbModels\GroupFlag;
use euu_customflag\DbModels\GroupProduct;
use euu_customflag\DbModels\CustomFlag;


class EuuCustomFlagsController extends ModuleAdminController {

	public $bootstrap = TRUE;


	public function initContent(){
		$this->content = $this->_renderFlagForm().$this->_renderFlagList();
		parent::initContent();
	}

	public function _renderFlagForm(){

		$flag_form = new DbModelForm(CustomFlag::class);

		$flag_form->setTitle($this->l('Flags'));
		$flag_form->setIcon('icon icon-list');
		$flag_form->showCancelButton();
		$flag_form->setSubmitButton(['title' => $this->l('Save'), 'icon' => 'process-icon-save']);
		$flag_form->setFormFields(
			array(
				'name' => [
					'col' => 3,
					'type' => 'text',
					'name' => 'name',
					'label' => $this->l('Flag Name'),
					'required' => true,
				],
				'text' => [
					'col' => 3,
					'type' => 'text',
					'name' => 'text',
					'label' => $this->l('Flag Text'),
					'lang' => true
				],
				'text_color' =>[
					'col' => 3,
					'type' => 'color',
					'name' => 'text_color',
					'label' => $this->l('Text Color'),
				],
				'text_size' =>[
					'col' => 3,
					'type' => 'text',
					'name' => 'text_size',
					'label' => $this->l('Text Size'),
				],
				'text_style' =>[
					'col' => 3,
					'type' => 'textarea',
					'name' => 'text_style',
					'label' => $this->l('Text Style'),
				],
				'bg_color' =>[
					'col' => 3,
					'type' => 'color',
					'name' => 'bg_color',
					'label' => $this->l('Background Color'),
				],
				'icon' =>[
					'col' => 3,
					'type' => 'text',
					'name' => 'icon',
					'label' => $this->l('Icon'),
				],
				'icon_color' =>[
					'col' => 3,
					'type' => 'color',
					'name' => 'icon_color',
					'label' => $this->l('Icon Color'),
				],
				'icon_size' =>[
					'col' => 3,
					'type' => 'text',
					'name' => 'icon_size',
					'label' => $this->l('Icon Size'),
				],
				'icon_style' =>[
					'col' => 3,
					'type' => 'textarea',
					'name' => 'icon_style',
					'label' => $this->l('Icon Style'),
				],
				'img' =>[
					'col' => 3,
					'type' => 'file_lang',
					'name' => 'img',
					'label' => $this->l('Image'),

					'max_file_size' => 1000,
					'allowed_extensions' => ['jpg','png'],

					'upload_dir' => _PS_IMG_DIR_,
					'upload_folder' => $this->module->name .'/FlagImages',
					'file_name' => function(string $upload_file_name,object $row){
						return '-'.$row->id.'-'.$upload_file_name;
					},

					'show_delete_button' => true,
					'show_image_thumb' => true,
					'thumb_size' => 150,

					'lang' => true
				],
				'img_width' =>[
					'col' => 3,
					'type' => 'text',
					'name' => 'img_width',
					'label' => $this->l('Image Width'),
				],
				'img_height' => [
					'col' => 3,
					'type' => 'text',
					'name' => 'img_height',
					'label' => $this->l('Image Height'),
				],
				'img_style' =>[
					'col' => 3,
					'type' => 'textarea',
					'name' => 'img_style',
					'label' => $this->l('Image Style'),
				],
				'is_active' =>[
					'name' => 'is_active',
					'type' => 'switch',
					'label' => $this->l('Active'),
					'required' => TRUE,
					'class' => 'fixed-width-xxl',
					'values' => [
						['id' => 'active_off', 'value' => 1, 'text' => 'Enabled'],
						['id' => 'active_on', 'value' => 0, 'label' => 'Disabled'],
					]
				],

			)
		);

		$flag_form->bindDeleteFile();
		$flag_form->bindSave();


		return $flag_form->renderForm();
	}

	public function _renderFlagList() {
		$custom_flag_list = new DbModelList(CustomFlag::class);
		$custom_flag_list->setRowActions(['edit','delete']);
		$custom_flag_list->setColumns(
			[
				'id_flag' => [
					'title' => 'ID',
					'search' => FALSE,
					'filter' => False
				],
				'name' => [
					'title' => $this->l('Flag Name'),
					'search' => FALSE,
					'filter' => False
				],
				'render' => [
					'title' => $this->l('Flag View'),
					'callback' => '_renderFlag',
					'query' => 'id_flag',
					'search' => FALSE,
					'filter' => False
				],
				'is_active' => [
					'title' => $this->l('Active'),
					'type' => 'bool',
					'class' => 'fixed-width',
					'filter_key' => '!is_active',
					'active'     => 'is_active',
					'search' => FALSE,
					'filter' => False
				],
			]
		);
		$custom_flag_list->bindToggleSwitch('is_active');
		$custom_flag_list->bindDeleteRow();
		return $custom_flag_list->renderList();
	}

	public function _renderFlag($id,$row){
		return CustomFlag::buildFlagAdmin($id);
	}

	public function postProcess(){
		$this->module->clearCssCache();
		parent::postProcess();
	}

}