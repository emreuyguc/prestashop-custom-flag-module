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
use Context;

if (!defined('_PS_VERSION_')) exit;

class CustomFlag extends DbModel {

	public $id;

	public $name;

	public $text;
	public $text_color;
	public $text_size;

	public $bg_color;

	public $icon;
	public $icon_color;
	public $icon_size;

	public $img;
	public $img_width;
	public $img_height;
	public $img_style;

	public $text_style;
	public $icon_style;

	//TODO HOVER BG COLOR HOVER ICON COLOR HOVER

	public $is_active;
	#date
	public $date_add;
	public $date_upd;

	protected static $_USE_PREFIX = TRUE;
	public static $definition = [
		'table' => ModuleConfigs::NAME . '_flags',
		'primary' => 'id_flag',
		//'multilang_shop' => true,
		'multilang' => true,
		'fields' => [
			'name' => [
				'type' => self::TYPE_STRING,
				'size' => 50,
				'required' => TRUE,
			],
			'text' => [
				'type' => self::TYPE_STRING,
				'size' => 50,
				'lang' => true
			],
			'text_color' => [
				'type' => self::TYPE_STRING,
				'size' => 20,
			],
			'text_size' => [
				'type' => self::TYPE_STRING,
				'size' => 20,
			],
			'icon'=> [
				'type' => self::TYPE_STRING,
				'size' => 50
			],
			'icon_color' => [
				'type' => self::TYPE_STRING,
				'size' => 20,
			],
			'icon_size' => [
				'type' => self::TYPE_STRING,
				'size' => 20,
			],
			'bg_color' => [
				'type' => self::TYPE_STRING,
				'size' => 20,
			],
			'text_style' => [
				'type' => self::TYPE_TEXT
			],
			'icon_style' => [
				'type' => self::TYPE_TEXT
			],
			'img' => [
				'type' => self::TYPE_STRING,
				'size' => 255,
				'lang' => true
			],
			'img_width' => [
				'type' => self::TYPE_STRING,
				'size' => 5
			],
			'img_height' => [
				'type' => self::TYPE_STRING,
				'size' => 5
			],
			'img_style' => [
				'type' => self::TYPE_TEXT,
			],
			'is_active' => [
				'type' => self::TYPE_BOOL,
                'required' => true
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

	public static function buildFlagAdmin($id) {
		$flag = new self($id);
		return '<div style="position: relative"><span class="label color_field" 
		style="'.
			   (!empty($flag->bg_color) ? 'background-color:'.$flag->bg_color.';' : '').
			   (!empty($flag->text_color) ? 'color:'.$flag->text_color.';' : '').
			   (!empty($flag->text_size) ? 'font-size:'.$flag->text_size.';' : '').
			   (!empty($flag->img[Context::getContext()->language->id]) ? 'display:block;width: 100px;height: 100px;background-size: contain;background-repeat: no-repeat;background-image:url(\''._PS_IMG_ . 'euu_customflag' . '/FlagImages/'.$flag->img[Context::getContext()->language->id].'\');' : '').
			   ($flag->text_style ?? '').
			   '">'.
			   ($flag->icon ? '<i class="fa fa-'.$flag->icon.'" style="color:'.$flag->icon_color.'; font-size:'.$flag->icon_size.'; '.($flag->icon_style ?? '').'" ></i> ' : '').
			   $flag->text[Context::getContext()->language->id].
			   '</span></div>';
	}


	public function delete(): bool {

        if(is_array($this->img)){
            foreach($this->img as $img){
                @unlink(_PS_IMG_DIR_ . ModuleConfigs::NAME . '/FlagImages/'.$img);
            }
        }
        else{
            @unlink(_PS_IMG_DIR_ . ModuleConfigs::NAME . '/FlagImages/'.$this->img);
        }

		GroupFlag::deleteRow('id_flag = '.$this->id);
		return parent::delete();
	}



}