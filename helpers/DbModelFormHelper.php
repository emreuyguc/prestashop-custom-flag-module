<?php
/**
 * @author emreuyguc <emreuyguc@gmail.com>
 * @copyright E.U.U 2022
 * @license Valid for 1 website (or project) and 1 domain only for each purchase of license
 * @package euu_customflag
 * @version 1.0.0
 *
 ** NOTICE OF LICENSE **
 *    This file is not open source ! Each license that you purchased is only available for 1 website (or project) and 1 domain only.
 *    If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 *    You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 ** DISCLAIMER **
 *    This SOFTWARE PRODUCT is provided by the PROVIDER "as is" and "with all defects".
 *    The PROVIDER makes no representations or warranties regarding the safety, suitability, absence of viruses, inaccuracies,
 * typographical errors or other harmful components of this SOFTWARE PRODUCT.
 *    The use of any software has its own risks and you are solely responsible for determining whether this SOFTWARE PRODUCT is
 * compatible with your system and other software installed on it.
 *    In addition, you are solely responsible for maintaining your system and backing up your data, and the PROVIDER will not be liable for
 * any damage you may suffer in connection with use or modification.
 *
 **/

namespace euu_customflag\Helpers;

use Tools;
use AdminController;
use Module;
use Context;
use Db;
use Validate;
use DBQuery;
use ImageManager;
use Configuration;
use Language;
/*
 * TODO
 * 	BIND LOAD
 *  AdminControllerdan processAdd ve processUpdate
 *
 *
 * 	LABEL LANG
 *  MESSAGE LANG
 */

class DbModelForm extends \HelperForm {

	public $model;
	private $_formFields = [];
	private $_buttons = [];
	private $_button_reset;
	private $_button_submit;
	private $_title;
	private $_icon;
	private $_tabId;

	public $bootstrap = TRUE;
	private $_msg_success = [];
	private $_msg_error = [];
	private $_msg_warning = [];
	private $_row_identifier;

	public function __construct(string $model_name) {
		parent::__construct();
        $this->modelName = $model_name;
		$this->model = new $model_name(Tools::getValue($model_name::$definition['primary']));

		$this->module = Module::getInstanceByName(
			Tools::getValue('controller') == 'AdminModules' ? $this->context->smarty->tpl_vars['module_name']->value : $this->context->controller->module->name
		);
		AdminController::$currentIndex .= (Tools::getValue('controller') == 'AdminModules' ? '&configure=' . Tools::getValue('configure') : '');

		$this->table = $this->model::$definition['table'];
		$this->identifier = $this->model::$definition['primary'];
		$this->id = $this->model->id;

		$this->_row_identifier = $this->model::$definition['table'];
		$this->show_toolbar = FALSE;
		$this->submit_action = (Tools::getValue($this->identifier) ? 'update' : 'insert') . ucfirst($this->_row_identifier);
		$this->currentIndex = AdminController::$currentIndex.(Tools::getValue($this->identifier) ? '&edit'.$this->table.'&'.$this->identifier.'='.Tools::getValue($this->identifier) : '');
		$this->token = $this->context->controller->token;

		$this->languages = $this->context->controller->getLanguages();
		$this->lang = true;
		$this->default_form_language = Configuration::get('PS_LANG_DEFAULT');
		//$this->allow_employee_form_lang = 1;


		/*
			todo hide, required, default val,get ,set , sanitaze , validate
			backurl
			toolbar btn
			name_controller
			description
			$this->context->smarty->assign( 'success', 'Success!' );
			controller...
		 */
	}

	public function renderForm() {

        foreach ($this->_formFields as &$field) {
            if ($field['type'] == 'file'){

                if(isset($field['show_image_thumb']) && $field['show_image_thumb'] == TRUE ){
                    $field['image'] = "";

                    if(!empty($this->model->{$field['name']})){
                        $field['image'] =  ImageManager::thumbnail(
                            $field['upload_dir']. $field['upload_folder'].'/' . $this->model->{$field['name']},
                            $this->model->{$field['name']},
                            $field['thumb_size']
                        );
                    }

                }

                if(isset($field['show_delete_button']) && $field['show_delete_button'] == TRUE){
                    $field['delete_url'] = $this->currentIndex.'&deleteFile='.$field['name'].'&token='.$this->token;
                }
            }
        }

		$this->tpl_vars = array(
			'fields_value' => $this->_loadFieldValues(
				array_combine(
					array_map(
						function ($field) {
							return $field['name'];
						},
						array_values($this->_formFields)
					),
					array_map(
						function ($field) {
							return $field['value'] ?? '';
						},
						array_values($this->_formFields)
					)
				)
			)
		);

		$this->token .= (!is_null($this->_tabId) ? '#' . $this->_tabId : '');

		return $this->generateForm(
			array(
				array(
					'form' => array(
						'legend' => array(
							'title' => $this->_title,
							'icon' => $this->_icon,
						),
						'input' => array_values($this->_formFields),
						'submit' => $this->_button_submit,
						'buttons' => $this->_buttons,
						'reset' => $this->_button_reset,
						'success' => implode("\n", array_values($this->_msg_success)),
						'error' => implode("\n", array_values($this->_msg_error)),
						'warning' => implode("\n", array_values($this->_msg_warning)),
					),
				),
			)
		);
	}

    private function _loadBlankLangValues(){
        $languages = Language::getLanguages(false);
        $lang_vals = [];
        foreach ($languages as $language){
            $lang_vals[$language['id_lang']] = '';
        }
        return $lang_vals;
    }
    private function _loadFieldValues(array $form_fields): array {
        foreach ($form_fields as $field => &$value) {
            //todo add default val from formfields array
            $value = $this->model->{$field} ?? (!empty($this->_formFields[$field]['lang']) ? $this->_loadBlankLangValues() : '');
        }

        return $form_fields;
    }

	public function setTitle(string $title) {
		$this->_title = $title;
		$this->title = $title;
	}

	public function setIcon(string $icon) {
		$this->_icon = $icon;
	}

	public function setTabName(string $tab) {
		$this->_tabId = $tab;
	}

	public function setButtons(array $buttons) {
		$this->_buttons = $buttons;
	}

	public function setFormFields(array $fields) {


		$this->_formFields = $fields;
	}

	public function setResetButton(array $button_parameteres) {
		$this->_button_reset = $button_parameteres;
	}

	public function showCancelButton() {
		$this->_buttons[] = ['title' => $this->l('Cancel'), 'icon' => 'process-icon-cancel','href' => AdminController::$currentIndex.'&token='.$this->token];
	}

	public function setSubmitButton(array $button_parameteres) {
		$this->_button_submit = $button_parameteres;
	}

	private function _getFieldValue($key, $id_lang = null,$is_file = false)
	{
        //todo required degilse ve boş veya hiç gelmez ise ?
        $field = $key . ($id_lang ? '_' . $id_lang : '');
		return $is_file ? (isset($_FILES[$field]) ? $_FILES[$field]['name'] : '' )  : Tools::getValue($field,'');
	}

	public function bindSave() {
		$file_upload_que = [];
		if (Tools::getValue('insert' . ucfirst($this->_row_identifier)) || Tools::getValue('update' . ucfirst($this->_row_identifier))) {

			$languages = Language::getLanguages(false);

			foreach ($this->model::$definition['fields'] as $field => $options) {
				if(isset($options['auto_date']) && in_array($options['auto_date'],['insert','update'])){
					continue;
				}

				if (
                    isset($this->_formFields[$field]['required']) && $this->_formFields[$field]['required'] == TRUE
                ) {
                    if(isset($options['lang']) && $options['lang'] == true){
                        foreach ($languages as $language) {
                            if(!strlen($this->_getFieldValue($field,$language['id_lang'],($this->_formFields[$field]['type'] == 'file' || $this->_formFields[$field]['type'] == 'file_lang')))){
                                $lang_iso = Language::getIsoById($language['id_lang']);
                                $req_lang_fields[] = ($this->_formFields[$field]['label'] . ' '. $lang_iso. $this->l(' field required ! '));
                            }
                        }
                        if (isset($req_lang_fields)) {
                            $this->_msg_error[$field]  = implode('<br />',$req_lang_fields);
                        }
                    }
                    else{
                        if(!strlen($this->_getFieldValue($field,null,($this->_formFields[$field]['type'] == 'file' || $this->_formFields[$field]['type'] == 'file_lang')))){
                            $this->_msg_error[$field] = ($this->_formFields[$field]['label'] . $this->l(' field required ! '));
                        }
                    }

                    if(isset($this->_msg_error[$field])){
                        continue;
                    }

				}


				if ($this->_formFields[$field]['type'] == 'file' || $this->_formFields[$field]['type'] == 'file_lang') {
					$file_upload_que[] = $field;
					continue;
				}

				if(isset($options['lang']) && $options['lang'] == true){
                    foreach ($languages as $language) {
                        $this->model->{$field}[(int) $language['id_lang']] = $this->_getFieldValue($field, $language['id_lang']);
                    }
				}else{
					$this->model->{$field} = $this->_getFieldValue($field);
				}

			}

			if (!count($this->_msg_error)) {
                if($this->model->save()){
                    //todo if file upload must be required , model save and if has error model rollback

                    $save_file = function($file_field,$id_lang = null) {
                        $post_field = $file_field . ($id_lang ? '_' . $id_lang : '');
                        if ($this->_validateFormFile($post_field,$id_lang)) {
                            $file_name = ($id_lang ? $id_lang.'_' : '').time().$this->_formFields[$file_field]['file_name']($_FILES[$post_field]['name'],$this->model);
                            if($this->_uploadFormFile($post_field,$file_name,$id_lang)){
                                if(!empty( ($id_lang ? ($this->model->{$file_field}[(int) $id_lang] ?? '') : $this->model->{$file_field}) )){
                                    @unlink($this->_formFields[$file_field]['upload_dir'] . $this->_formFields[$file_field]['upload_folder'].'/'.(($id_lang ? $this->model->{$file_field}[(int) $id_lang] : $this->model->{$file_field})));
                                }
                                if($id_lang){
                                    $this->model->{$file_field}[(int) $id_lang] = $file_name;
                                }else{
                                    $this->model->{$file_field} = $file_name;
                                }

                                $this->model->save();
                            }
                        }
                    };

                    foreach ($file_upload_que as $file_field){

                        if(isset($this->_formFields[$file_field]['lang']) && $this->_formFields[$file_field]['lang'] == true){
                            foreach ($languages as $lang){
                                $save_file($file_field,$lang['id_lang']);
                            }

                        }else{
                            $save_file($file_field);
                        }


                    }

                    die(Tools::redirectAdmin(AdminController::$currentIndex.'&token='.$this->token));
                    //$this->context->link->getAdminLink($this->context->controller->controller_name, true)
                }

            }
		}
	}

	private function _getActionLink($params = NULL, $action = NULL): string {
		if ($params) {
			$query_string = '';
			foreach ($params as $param => $val) {
				$query_string .= $param . '=' . $val . '&';
			}
			$query_string = rtrim($query_string, '&');
		}


		return $this->context->link->getAdminLink(
				$this->context->controller->controller_name,
				TRUE
			) . (Tools::getValue('controller') == 'AdminModules' ? '&configure=' . Tools::getValue('configure') : '') . (!is_null(
				$action
			) ? '&' . $action . $this->table : NULL) . (!is_null($params) ? '&' . $query_string : NULL) . (!is_null($this->_tabId) ? '#' . $this->_tabId : NULL);
	}

	private function _validateFormFile(string $field,bool $id_lang = null) {

		if($id_lang){
            $exp = explode('_',$field,2);
            $field_key = $exp[0];
			$lang_iso = Language::getIsoById($id_lang);
		}
		else{
			$field_key = $field;
		}

		if (!isset($_FILES[$field]) || (isset($_FILES[$field]) && empty($_FILES[$field]['name']))) {
			return FALSE;
		}
		$_msg_upload_err = [
			1 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file size bigger than php.ini upload_max_filesize value'),
			2 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file exceeds the MAX_FILE_SIZE specified in the HTML form.'),
			3 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file is partially uploaded.'),
			4 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file has not been uploaded.'),
			6 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file upload error. There is no temporary directory.'),
			7 => $this->_formFields[$field_key]['label'] .($id_lang ? $lang_iso : '') .$this->l(' file could not be written to disk.')
		];
		if ($_FILES[$field]['error'] != 0 ) {
			$this->_msg_warning[$field][] = isset($_msg_upload_err[$_FILES[$field]['error']]) ? $_msg_upload_err[$_FILES[$field]['error']] : $this->l('unkown error !');
		} else {
			if (($_FILES[$field]['size'] / 1000) > $this->_formFields[$field_key]['max_file_size']) {
				$this->_msg_warning[$field][] = ($this->_formFields[$field_key]['label'] .sprintf($this->l(" file size maximum %s kb  ! "), $this->_formFields[$field_key]['max_file_size']) );
			}

			$file_extension = explode('.', $_FILES[$field]['name'], 2)[1];
			if (!in_array($file_extension, $this->_formFields[$field_key]['allowed_extensions'], TRUE)) {
				$this->_msg_warning[$field][] = ($this->_formFields[$field_key]['label'] .
												 $this->l(' file Acceptable extensions : ') .
												 implode(', ', $this->_formFields[$field_key]['allowed_extensions']) .
												 '');
			}
		}
		if (isset($this->_msg_warning[$field])) {
			$this->_msg_warning[$field] = implode('<br />', $this->_msg_warning[$field]);
		}

		return !isset($this->_msg_warning[$field]);
	}

	private function _uploadFormFile(string $field,string $file_name,bool $id_lang = null) {
		if($id_lang){
			$exp = explode('_',$field,2);
			$field_key = $exp[0];
		}else{
			$field_key = $field;
		}

		$path = $this->_formFields[$field_key]['upload_dir'] .  $this->_formFields[$field_key]['upload_folder'].'/';
		if (!is_dir($path)){
			if(!mkdir($path, 0777, TRUE) && !is_dir($path)){
				throw new \RuntimeException($this->l('Directory "%s" was not created', $path));
			}
		} else {
			if (isset($this->_formFields[$field_key]['image'])) {
				$image_info = getimagesize($_FILES[$field]['tmp_name']); //note check err
				$temp_path = tempnam(_PS_TMP_IMG_DIR_, 'TEMP_');
				if(!move_uploaded_file($_FILES[$field]['tmp_name'], $temp_path)){
					$this->_msg_warning[$field][] = $this->l(' temp move failed !');
				}
				else if(!ImageManager::resize(
					$temp_path,
					$path.$file_name,
					$image_info[0],
					$image_info[1],
					explode('.', $_FILES[$field]['name'], 2)[1] //note check err
				)){
					$this->_msg_warning[$field][] = $this->l(' resize failed !');
				}

			} else {
				if(!move_uploaded_file($_FILES[$field]['tmp_name'], $path.$file_name)){
					$this->_msg_warning[$field][] = $this->l(' copy failed !');
				}
			}
		}

		if (isset($this->_msg_warning[$field])) {
			$this->_msg_warning[$field] = implode('<br />', $this->_msg_warning[$field]);
		}

		return !isset($this->_msg_warning[$field]);
	}

	public function bindDeleteFile(){
		if(Tools::getIsset('deleteFile') && $this->id){
            $file_field = Tools::getValue('deleteFile');
            $id_lang = Tools::getValue('fileLang');

            $file_path = $this->_formFields[$file_field]['upload_dir'] . $this->_formFields[$file_field]['upload_folder'].'/'.(($id_lang ? $this->model->{$file_field}[(int) $id_lang] : $this->model->{$file_field}));
            if(is_file($file_path)){
                @unlink($file_path);
            }
            $id_lang ? $this->model->{$file_field}[(int) $id_lang] = null : $this->model->{$file_field} = null;
            $this->model->save();

		}
	}
}