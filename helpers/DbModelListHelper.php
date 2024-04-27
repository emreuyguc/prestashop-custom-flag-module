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

namespace euu_customflag\Helpers;


use Tools;
use AdminController;
use Module;
use Context;
use Db;
use Validate;
use DBQuery;


/*
 * todo  multilng için kolay görntu
 *
 *
 *todo
 *  ajax delete
 *  ajax add
 *  ajax detail
 *	jsonConfirmation presta bug
 *
 */


class DbModelList extends \HelperList {
	private $_columns;
	private $_model;
	private $_where;
	private $_order;
	private $_limit;

	private $_rows;

	private $_tabId;
	private $_actionMethods;

	public $bootstrap = TRUE;


	public function __construct(string $model_name) {
		parent::__construct();

		$this->_model = new $model_name();
		$this->module = Module::getInstanceByName(
			Tools::getValue('controller') == 'AdminModules' ? $this->context->smarty->tpl_vars['module_name']->value : $this->context->controller->module->name
		);
		AdminController::$currentIndex .= (Tools::getValue('controller') == 'AdminModules' ? '&configure=' . Tools::getValue('configure') : '');
		$this->token = $this->context->controller->token;
		$this->currentIndex = AdminController::$currentIndex;
		$this->table = $this->_model::$definition['table'];
		$this->identifier = $this->_model::$definition['primary'];
		$this->list_id = $this->table;
		$this->className = get_class($this->_model);

		/*
			delete
			defaultOrderBy
			defaultOrderWay

			filter
			_select
			simple_header
			show_toolbar


    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }
 controllerda

		*/

		//note default actions
		//note allowd or declined
		//todo bindaction display...link yerine function return veya class bind
		//todo quikedit


		$this->actions = [
			'edit',
			'delete',
			'detail',
			'quickDetail',
		];

		$this->simple_header = FALSE; //note For showing add and refresh button
		$this->shopLinkType = '';
		$this->show_toolbar = FALSE;//???
		$this->no_link = TRUE;

		$this->orderBy = $this->_model::$definition['primary'];
		$this->orderWay = 'desc';
		$this->position_identifier = 'position';


	}



	public function setColumns(array $columns) {
		$this->_columns = $columns;
	}

	public function addRowAction(string $action, callable $actionMethod) {
		$this->actions[] = $action;
	}

	public function setRowActions(array $actions) {
		$this->actions = $actions;
	}

	public function renderList() {

		if (Tools::getValue($this->list_id . '_pagination')) {
			$offset = (int)Tools::getValue($this->list_id . '_pagination');
			if (in_array($offset, $this->_pagination) && $offset != $this->_default_pagination) {
				$this->context->cookie->{$this->list_id . '_pagination'} = $offset;
			} else {
				unset($this->context->cookie->{$this->list_id . '_pagination'});
			}
		} else {
			if (isset($this->context->cookie->{$this->list_id . '_pagination'}) && $this->context->cookie->{$this->list_id . '_pagination'}) {
				$offset = $this->context->cookie->{$this->list_id . '_pagination'};
			} else {
				$offset = $this->_default_pagination;
			}
		}

		$start = 0;
		if ((int)Tools::getValue('submitFilter' . $this->list_id)) {
			$start = ((int)Tools::getValue('submitFilter' . $this->list_id) - 1) * $offset;
		} else if (empty($start) && isset($this->context->cookie->{$this->list_id . '_start'}) && Tools::isSubmit('export' . $this->table)) {
			$start = $this->context->cookie->{$this->list_id . '_start'};
		}

		// Either save or reset the offset in the cookie
		if ($start) {
			$this->context->cookie->{$this->list_id . '_start'} = $start;
		} else if (isset($this->context->cookie->{$this->list_id . '_start'})) {
			unset($this->context->cookie->{$this->list_id . '_start'});
		}

		$this->_rows = $this->_rows ?? $this->_model::select(array_map(function ($column,$key){
				if(isset($column['query'])){
					return ['query' => $column['query'], 'as' => $key];
				}
				return $key;
			},($this->_columns),array_keys($this->_columns)),$this->_where, null,$this->_order ?? [[$this->orderBy, $this->orderWay]], $start, $offset);
		$this->listTotal = $this->_rowCount ??  ($this->_model::selectValue('count(*)',$this->_where, $this->_order ?? [[$this->orderBy, $this->orderWay]]));

		return parent::generateList(
			$this->_rows,
			$this->_columns
		);
	}

	public function setRowCount($count){
		$this->_rowCount = $count;
	}

	public function setTitle(string $title) {
		$this->title = $title;
	}

	public function setRows(array $data){
		$this->_rows = $data;
	}

	public function displayDeleteLink($token, $id, $name = NULL) {
		return $this->_generateRowActionButton(
			'#delete',
			'Delete',
			'trash',
			TRUE,
			'Delete selected item?',
			$this->_getActionLink([$this->_model::$definition['primary'] => $id], 'delete')
		);
	}

	public function displayEditLink($token, $id, $name = NULL) {
		return $this->_generateRowActionButton($this->_getActionLink([$this->_model::$definition['primary'] => $id], 'edit'), 'Edit', 'pencil');
	}

	public function displayDetailLink($token, $id, $name = NULL) {
		return $this->_generateRowActionButton($this->_getActionLink([$this->_model::$definition['primary'] => $id], 'detail'), 'Detail', 'eye');
	}

	public function displayQuickDetailLink($token, $id, $name = NULL) {
		return $this->_generateRowActionButton('#quickDetail', 'Quick Detail', 'search-plus');
	}

	public function setTabName(string $tabId) {
		$this->_tabId = $tabId;
		$this->list_id = $this->_tabId;
	}

	public function setTableName(string $table_id) {
		$this->table_id = $table_id . '-dnd';
	}

	//todo maybe public , getTabLink getControllerLink vs , getRowAction link veya getTableAction link
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

	//todo make,generate,create,render
	private function _generateRowActionButton($href, $title, $icon, $confirm = FALSE, $confirm_msg = '', $confirm_link = '', $cancel_link = '#') {
		$tpl = $this->context->smarty->createTemplate($this->module->getLocalPath() . 'views/templates/admin/helper/list/list-action-button-link.tpl');
		$tpl->assign(
			[
				'action' => [
					'href' => $href,
					'title' => Context::getContext()
									  ->getTranslator()
									  ->trans($title, array(), 'Admin.Global'),
					'icon' => $icon,
					'confirm' => $confirm,
					'confirm_msg' => Context::getContext()
											->getTranslator()
											->trans($confirm_msg, array(), 'Admin.Global'),
					'confirm_link' => $confirm_link,
					'cancel_link' => $cancel_link
				]
			]
		);

		return $tpl->fetch();
	}

	public function bindAjaxUpdatePosition() {
		self::bindAjaxUpdatePositions($this->className);
	}

	public static function bindAjaxUpdatePositions(string $model) {
		$context = Context::getContext();
		$identifier = $model::$definition['primary'];

		if ($context->controller->ajax && Tools::getValue('action') == 'updatePositions' && Tools::getIsset(explode('_', $identifier, '2')[1])) {
			$positions = Tools::getValue(explode('_', $identifier, '2')[1]);
			$sql = '';
			foreach ($positions as $index => $row) {
				$id = explode('_', $row)[2];
				$sql .= "UPDATE {$model::getTableName()} SET position = {$index} WHERE {$identifier} = {$id};";
			}
			try {
				if (Db::getInstance()
					  ->execute($sql)) {
					$context->controller->jsonConfirmation($context->controller->module->l('Update position success.'));
				} else {
					header("HTTP/1.1 500 Internal Server Error");
				}
			} catch (Exception $e) {
				header("HTTP/1.1 500 Internal Server Error");
			}

		}

	}

	public function bindDeleteRow() {
		if (Tools::getIsset('delete' . $this->table) && Validate::isLoadedObject(
				$obj = new $this->_model(
					Tools::getValue($this->identifier)
				)
			)) {
			$obj->delete();
			Tools::redirect($this->_getActionLink());
		}
	}

	public static function bindAjaxDeleteRow(string $model) {
		//todo without position
		$context = Context::getContext();
		$identifier = explode('_', $model::$definition['primary'], '2')[1];
		if ($context->controller->ajax && Tools::getValue('action') == 'deleteRow' && Tools::getIsset($identifier)){
			if(Validate::isLoadedObject(
				$obj = new $model(
					Tools::getValue($identifier)
				)
			)){
				$obj->delete();
				$context->controller->content = [
					'id' => $obj->id,
					'position' => $obj->position
				];
				$context->controller->jsonConfirmation($context->controller->module->l('Delete row success.'));
			}
		}
	}

	//todo inner joined
	public function bindOrder() {
		if (Tools::getIsset($this->list_id . 'Orderby') && Tools::getIsset($this->list_id . 'Orderway')) {
			$this->_order = array([pSQL(Tools::getValue($this->list_id . 'Orderby')), pSQL(Tools::getValue($this->list_id . 'Orderway'))]);
		}
	}

	public function bindFilter() {

		if(Tools::getValue('submitFilter'.$this->list_id) == 1){
			foreach (Tools::getAllValues() as $key => $value){
				//TODO PRESTA 1.7 İÇİN REVİZE
				if(!empty($value)){
					if(strpos($key,'_') !== FALSE && explode('_',$key)[0] == $this->list_id.'Filter'){
						$column = explode('_',$key)[1];
						if($column == $this->position_identifier && is_numeric($value)){
							$value -= 1;
						}
						$this->_where .= 'AND `'.$column . '` '.(strpos($value,'%') !== FALSE ? 'like' : '=').' "'.pSQL($value).'" ';
					}
				}
			}
			if($this->_where && substr(trim($this->_where), 0,3) === 'AND'){
				$this->_where = substr($this->_where,3);
			}
		}

		if(Tools::getIsset('submitReset'.$this->list_id)){
			foreach ($_POST as $key => &$value){
				if(!empty($value)){
					if(strpos($key,'_') !== FALSE && explode('_',$key)[0] == $this->list_id.'Filter'){
						$value = '';
					}
				}
			}
		}
	}

	//todo inner joined
	public function bindToggleSwitch(string $toggle_column) {
		if(Tools::getIsset($toggle_column.$this->table) && Validate::isLoadedObject($obj = new $this->_model(Tools::getValue($this->identifier)))){
			$obj->toggleColumn($toggle_column);
			Tools::redirect($this->_getActionLink());
		}
	}

	public static function bindAjaxToggleSwitch(string $model,string $toggle_column) {
		$context = Context::getContext();
		//todo with row
		if(Tools::getIsset($toggle_column.$model::$definition['table']) && Validate::isLoadedObject($obj = new $model(Tools::getValue($model::$definition['primary'])))){
			if($obj->toggleColumn($toggle_column)){
				exit(json_encode(['success' => 1,'message' => $context->controller->module->l('Update status success.')]));
			}else{
				exit(json_encode([
									 'success' => 0,
									 'message' => $context->controller->module->l('Update status fail.')
								 ]));
			}
		}
	}


	public function setWhere(string $sql) {
		$this->_where = $sql;
	}

	public function setOrder(array $column_way) {
		$this->_order = $column_way;
	}

	public function setDragRows() {
		$this->orderBy = 'position';
	}

	public function setToolbarBtn($key,array $params) {
		$this->toolbar_btn[$key] = $params;
	}

}