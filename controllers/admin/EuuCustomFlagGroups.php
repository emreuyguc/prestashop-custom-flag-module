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

use euu_customflag\DbModels\CustomFlag;
use euu_customflag\DbModels\GroupFlag;
use euu_customflag\DbModels\GroupProduct;
use euu_customflag\Helpers\DbModelList;

use function euu_customflag\Utils\sql_replace;

class EuuCustomFlagGroupsController extends ModuleAdminController
{

    public $bootstrap = TRUE;

    public function __construct()
    {
        parent::__construct();

        $this->_main_list_model = FlagGroup::$definition;
        $this->_sub_list_model = GroupFlag::$definition;

        $this->_setFlagGroupForm();
        $this->_setFlagGroupList();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        if ($this->display == 'add' || $this->display == 'edit') {
            $this->page_header_toolbar_btn['back_to_list'] = [
                'desc' => $this->l('Back'),
                'icon' => 'process-icon-back',
                'href' => $this->context->link->getAdminLink('EuuCustomFlagGroups', true)
            ];
        } else {
            $this->page_header_toolbar_btn['add'] = [
                'desc' => $this->l('Add'),
                'icon' => 'process-icon-new',
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token
            ];
        }


    }

    public function initContent()
    {
        $this->addJs($this->module->getPathUri() . 'views/assets/js/global.js');
        $this->addJs($this->getTemplatePath() . 'flag-group/page.js');
        $this->addCss($this->getTemplatePath() . 'flag-group/page-style.css');
        parent::initContent();
        /* @inheritdoc here this->content fill with smarty assign with display variable filter */
    }

    public function renderForm()
    {
        //this way return or content variable direct manipulation
        //if display == edit
        $content = parent::renderForm();

        if (Tools::getIsset('id_flag_group')) {
            $this->_setGroupFlagModal();
            $this->_setRunQueryModal();

            $content .= $this->_renderQueryBuilder() . $this->_renderGroupFlagList();
        }

        return $content;
    }

    public function display()
    {
        //here this->content fill with smarty assign
        parent::display();
    }

    public function displayAjax()
    {
        // @inheritDoc for tpl bug
        if ($this->json) {
            $this->context->smarty->assign(array(
                'json' => true,
                'status' => $this->status,
            ));
        }
        $this->layout = $this->getTemplatePath() . 'helper/layout/layout-ajax.tpl';
        $this->display_header = false;
        $this->display_header_javascript = false;
        $this->display_footer = false;

        return $this->display();
    }

    /** @GROUP MAIN LIST ** */
    private function _setFlagGroupForm()
    {

        $this->className = FlagGroup::class;
        $this->table = FlagGroup::$definition['table'];
        $this->identifier = FlagGroup::$definition['primary'];

        $this->show_form_cancel_button = FALSE;

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Flag Group Form'),
                'icon' => 'icon-list-ul'
            ],
            'input' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'required' => TRUE,
                    'class' => 'fixed-width-xxl'
                ],
                [
                    'name' => 'date_start',
                    'type' => 'date',
                    'label' => $this->l('Date Start'),
                ],
                [
                    'name' => 'date_end',
                    'type' => 'date',
                    'label' => $this->l('Date End'),
                ],
                [
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
                'sql' => [
                    'name' => 'sql',
                    'type' => 'textarea',
                    'label' => '',//$this->l('Flag Products Sql'),
                    'class' => 'hidden'
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
            'buttons' => [
                [
                    'href' => $this->context->link->getAdminLink($this->controller_name, TRUE),
                    'class' => 'btn btn-default',
                    'icon' => 'process-icon-cancel',
                    'title' => $this->l('Back'),
                    'name' => 'back'
                ]
            ]
        ];
    }

    private function _setFlagGroupList()
    {

        $this->_defaultOrderBy = $this->identifier; //todo BUNA ARKAPLANDA BAK
        $this->_defaultOrderWay = 'DESC';

        $this->list_no_link = TRUE;

        $this->_select = '(select GROUP_CONCAT(id_flag) FROM ' . _DB_PREFIX_ . GroupFlag::$definition['table'] . ' WHERE id_flag_group = a.id_flag_group ) AS flags,';
        $this->_select .= '(select count(id_product) FROM ' . _DB_PREFIX_ . GroupProduct::$definition['table'] . ' WHERE id_flag_group = a.id_flag_group ) AS product_count';


        $this->actions = [
            'edit',
            'delete'
        ];

        $this->fields_list = array(
            $this->identifier => [
                'title' => 'ID',
                'type' => 'int',
                'filter' => FALSE,
                'search' => FALSE,
                'class' => 'fixed-width-xs'
            ],
            'title' => [
                'title' => $this->l('Title'),
                'type' => 'string',
                'class' => 'fixed-width'
            ],
            'date_start' => [
                'title' => $this->l('Start Date'),
                'type' => 'date',
                'class' => 'fixed-width-sm',
                'callback' => '_renderDateStart'
            ],
            'date_end' => [
                'title' => $this->l('End Date'),
                'type' => 'date',
                'class' => 'fixed-width-sm',
                'callback' => '_renderDateEnd'
            ],
            'flags' => [
                'title' => $this->l('Flags'),
                'type' => 'string',
                'filter' => FALSE,
                'search' => FALSE,
                'callback' => '_renderFlag'
            ],
            'product_count' => [
                'title' => $this->l('Product Count'),
                'type' => 'int',
                'filter' => FALSE,
                'search' => FALSE,
                'class' => 'fixed-width-sm',
                'callback' => '_renderProductCount'
            ],
            'is_active' => [
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'is_active',
                'ajax' => true
            ],
        );
    }

    public function _renderDateStart($date)
    {
        if ($date == '0000-00-00 00:00:00') return 'No date set';

        return date('Y-m-d H:i:s') < $date ? '<span class="label color_field" style="background-color: #a94442">' . $date . '</span>' : $date;
    }

    public function _renderDateEnd($date)
    {
        if ($date == '0000-00-00 00:00:00') return 'No date set';

        return date('Y-m-d H:i:s') > $date ? '<span class="label color_field" style="background-color: #a94442">' . $date . '</span>' : $date;
    }

    public function _renderProductCount($count)
    {
        return '<span class="badge" style="font-weight: bold">' . $count . '</span>';
    }

    public function _renderFlag($id_flags)
    {
        $id_flags = explode(',', $id_flags);
        $output = '';
        foreach ($id_flags as $id_flag) {
            $output .= CustomFlag::buildFlagAdmin($id_flag) . '<br />';
        }

        return $output;
    }

    /** @GROUP SUB LIST ** */
    private function _renderGroupFlagList()
    {

        $group_flag_list = new DbModelList(GroupFlag::class);
        $group_flag_list->setTitle($this->l('Group Flags'));
        $group_flag_list->setDragRows();
        $group_flag_list->orderWay = 'asc';
        $group_flag_list->setToolbarBtn(
            'new',
            [
                'href' => '#',
                'js' => "$('#group-flag-modal').modal('toggle')",
                'desc' => $this->l('Add Flag')
            ]
        );
        $group_flag_list->setRowActions(['deleteGroupFlag']);
        $group_flag_list->setWhere('id_flag_group = ' . pSQL(Tools::getValue('id_flag_group')));
        $group_flag_list->setColumns(
            array(
                $group_flag_list->identifier => [
                    'title' => 'ID',
                    'type' => 'int',
                    'filter' => FALSE,
                    'search' => FALSE,
                    'class' => 'fixed-width-xs'
                ],
                'id_flag' => [
                    'title' => $this->l('Flag'),
                    'type' => 'text',
                    'filter' => FALSE,
                    'search' => FALSE,
                    'callback' => '_renderFlag'
                ],
                'position' => [
                    'title' => $this->l('Position'),
                    'type' => 'int',
                    'filter' => FALSE,
                    'search' => FALSE,
                    'position' => 'position',
                ],

            )
        );

        return $group_flag_list->renderList();
    }

    public function displayDeleteGroupFlagLink($token, $id_data)
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'helper/list/list-action-button.tpl');
        $tpl->assign(
            [
                'action' => [
                    'href' => '#',
                    'title' => $this->l('Delete'),
                    'confirm' => TRUE,
                    'confirm_msg' => $this->l('are u sure for delete ?'),
                    'confirm_action' => '() => deleteGroupFlag(' . $id_data . ')',
                    'cancel_action' => FALSE,
                    'icon' => 'trash'
                ]
            ]
        );

        return $tpl->fetch();
    }

    private function _setGroupFlagModal()
    {
        $modal = $this->context->smarty->createTemplate($this->getTemplatePath() . 'flag-group/group-flag-modal.tpl');
        $modal->assign(
            [
                'flags' => CustomFlag::select(NULL, 'is_active = 1')
            ]
        );

        $this->modals[] = [
            'modal_id' => 'group-flag-modal',
            'modal_class' => '',
            'modal_title' => $this->l('Group Flag'),
            'modal_content' => $modal->fetch(),
        ];
    }

    private function _setRunQueryModal()
    {
        $this->modals[] = [
            'modal_id' => 'modalRunQuery',
            'modal_class' => '',
            'modal_title' => $this->l('Run Query'),
            'modal_content' => '
		<div  class="modal-body defaultForm form-horizontal">
	 <div id="resultRunQuery" class="alert">' . $this->l('Processing...') . '</div> 
	 </div>',
        ];
    }

    /** @GROUP AJAX ** */
    public function ajaxPreProcess()
    {
        DbModelList::bindAjaxUpdatePositions(GroupFlag::class);
        DbModelList::bindAjaxDeleteRow(GroupFlag::class);
        DbModelList::bindAjaxToggleSwitch(FlagGroup::class, 'is_active');

        /* @inheritdoc prestashop native ajax response
         * but 1.7.7.0
         * confirmations: ["Update Position OK"]
         * content: null
         * error: []
         * informations: []
         * status: "ok"
         * warnings: []
         */

        if (Tools::getIsset('query_action') && Tools::getIsset('query')) {
            $id_flag_group = Tools::getValue('id_flag_group');

            $this->ajaxAction = Tools::getValue('action');
            /** SQL SECURITY **/
            $this->_queryBuilderQuery = str_replace(['UPDATE', 'DELETE', 'INSERT', 'TRUNCATE', 'SELECT'], '', Tools::getValue('query'));
            $this->_queryBuilderSql = _DB_PREFIX_ .
                'product as product ' .
                $this->_queryJoinToString($this->_getQueryBuilderJoins()) .
                ' WHERE 1=1 AND ' .
                $this->_queryBuilderQuery .
                $this->_getQueryBuilderWhere();

            if ($this->ajaxAction == 'checkQuery') {
                $this->_queryBuilderSql = 'SELECT count(DISTINCT(product.id_product)) FROM ' . $this->_queryBuilderSql;
            } else if ($this->ajaxAction == 'runQuery') {
                $this->_queryBuilderSql = 'SELECT DISTINCT(product.id_product) as id_product,' . $id_flag_group . ' as id_flag_group FROM ' . $this->_queryBuilderSql;
            }
        }
    }

    public function ajaxProcessSearchProductByName()
    {
        if (Tools::getIsset('search')) {
            $name = Tools::getValue('search');

            $search_query = Db::getInstance()
                ->executeS(
                    sql_replace(
                        'SELECT id_product as id,name FROM ' . _DB_PREFIX_ . 'product_lang WHERE name like ? and id_lang = ? LIMIT 20',
                        [
                            '%' . pSQL($name) . '%',
                            $this->context->language->id
                        ]
                    )
                );

            $this->ajaxDie(json_encode($search_query));
        }
    }

    public function ajaxProcessCheckQuery()
    {
        if (Tools::getIsset('query_action') && Tools::getIsset('query')) {
            try {
                $select_row_count = Db::getInstance()
                    ->getValue($this->_queryBuilderSql);
                /* @inheritDoc  prestashop native ajax response */
                $this->content = [
                    'affected_row_count' => $select_row_count
                ];
                $this->jsonConfirmation(
                    sprintf($this->l("%s Rows will be affected by query. Do you want run query?"), '<span style="color: red">' . $select_row_count . '</span>')
                );
            } catch (Exception $e) {
                $this->jsonError($this->l('Query Error : ') . $e->getMessage());
            }
        }
    }

    public function ajaxProcessRunQuery()
    {
        if (Tools::getIsset('query_action') && Tools::getIsset('query') && Tools::getIsset('id_flag_group')) {
            $id_flag_group = Tools::getValue('id_flag_group');
            $query_action = Tools::getValue('query_action');
            if ($query_action == 'insert' || $query_action == 'replace' || $query_action == 'delete') {
                if ($query_action != 'insert') {
                    //TODO CLEAR AND DELETE BY QUERY
                    $result = GroupProduct::deleteRow(sql_replace('id_flag_group = ?', [$id_flag_group]));
                }
                if ($query_action == 'replace' || $query_action == 'insert') {
                    $result = Db::getInstance()
                        ->execute('INSERT INTO ' . _DB_PREFIX_ . GroupProduct::$definition['table'] . ' (`id_product`, `id_flag_group`) ' . $this->_queryBuilderSql . ';');
                }
            }

            if ($result == TRUE) {
                $this->jsonConfirmation($this->l('Query Success'));
                $flag_group = (new FlagGroup($id_flag_group));
                $flag_group->sql = $this->_queryBuilderQuery;
                $flag_group->save();
            } else {
                $this->jsonError($this->l('Query Error'));
            }
        }
    }

    /** @GROUP POST * */
    public function postProcess()
    {
        /* PRESTA EMPTY ACTION BUG */
        if (empty($this->action)) {
            $this->action = Tools::getValue('action');
        }
        /* PRESTA EMPTY ACTION BUG */


        parent::postProcess();
    }

    public function processSave(){
        parent::processSave();
        $this->redirect_after = '';
    }

    public function processSaveGroupFlag()
    {
        if (Tools::getValue('id_flag_group') && Tools::getValue('id_flag')) {
            $group_flag = new GroupFlag();
            $group_flag->id_flag = Tools::getValue('id_flag');
            $group_flag->id_flag_group = Tools::getValue('id_flag_group');


            $last_pos = GroupFlag::selectValue('max(position)', 'id_flag_group = ' . $group_flag->id_flag_group) ?? -1;
            $group_flag->position = $last_pos + 1;
            $group_flag->save();
        }
    }

    /** @GROUP QUERY BUILDER * */
    public function _renderQueryBuilder()
    {
        $this->addJs($this->module->getPathUri() . 'views/assets/lib/SqlParser/sql-parser.min.js');
        $this->addJs($this->module->getPathUri() . 'views/assets/lib/Selectize/js/standalone/selectize.min.js');
        $this->addJs($this->module->getPathUri() . 'views/assets/lib/QueryBuilder/js/query-builder.standalone.min.js');
        $this->addJs($this->module->getPathUri() . 'views/assets/lib/QueryBuilder/i18n/query-builder.' . $this->context->language->iso_code . '.js');
        $this->addJs($this->module->getPathUri() . 'views/assets/lib/BootBox/bootbox.min.js');

        $this->addJs($this->module->getPathUri() . 'views/assets/lib/Moment/moment.js');

        $this->addJs($this->module->getPathUri() . 'views/assets/lib/BootstrapDatepicker/bootstrap-datetimepicker.min.js');
        $this->addCss($this->module->getPathUri() . 'views/assets/lib/BootstrapDatepicker/bootstrap-datetimepicker.min.css');


        $this->addCss($this->module->getPathUri() . 'views/assets/lib/Selectize/css/selectize.bootstrap3.css');
        $this->addCss($this->module->getPathUri() . 'views/assets/lib/QueryBuilder/css/query-builder.default.min.css');

        $panel = $this->context->smarty->createTemplate($this->getTemplatePath() . 'helper/panel/panel.tpl');
        $query_builder = $this->context->smarty->createTemplate($this->getTemplatePath() . 'flag-group/flag-group-query-builder.tpl');

        $query_builder->assign(
            [
                'filters' => $this->_getQueryBuilderFilters()
            ]
        );

        $panel->assign(
            [
                'title' => $this->l('Query Builder'),
                'content_body' => $query_builder->fetch(),
                'content_footer' => '<form method="post" action="" id="formQuery" class="pull-right" style="display: flex;">' . '<input type="hidden" name="query">' . '<select class="fixed-width-lg" name="query_action">
						<option value="replace">' . $this->l('Replace') . '</option>
						<option value="insert">' . $this->l('Insert') . '</option>
						<option value="delete">' . $this->l('Delete') . '</option>
					</select>' . '<button  type="submit" name="action" value="checkQuery">' . $this->l('Check & Run Query') . '</button>' . '</form>'
            ]
        );

        return $panel->fetch();
    }

    private function _getQueryBuilderJoins(): array
    {
        return [
            /*
             * 'sellers' => [
                [
                    'join_type' => 'INNER',
                    'table' => _DB_PREFIX_ . 'ets_mp_seller_product',
                    'as' => 'seller_product',
                    'on' => ['product.id_product' => 'seller_product.id_product'],
                ],
                [
                    'join_type' => 'INNER',
                    'table' => _DB_PREFIX_ . 'ets_mp_seller',
                    'as' => 'seller',
                    'on' => ['seller_product.id_customer' => 'seller.id_customer'],
                ]
            ],
             */
            'categories' => [
                [
                    'join_type' => 'INNER',
                    'table' => _DB_PREFIX_ . 'category_product',
                    'as' => 'category_product',
                    'on' => ['product.id_product' => 'category_product.id_product',]
                ],
                [
                    'join_type' => 'INNER',
                    'table' => _DB_PREFIX_ . 'category',
                    'as' => 'category',
                    'on' => ['category_product.id_category' => 'category.id_category']
                ],
            ],
            'stock' => [
                [
                    'join_type' => 'INNER',
                    'table' => _DB_PREFIX_ . 'stock_available',
                    'as' => 'stock_available',
                    'on' => ['product.id_product' => 'stock_available.id_product',]
                ]
            ]
        ];
    }

    private function _getQueryBuilderFilters(): array
    {
        return [
            //NOTE MAIN
            'product.id_product' => [
                'field' => 'product.id_product',
                'label' => $this->l('Products'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => 1,
                'plugin' => 'selectize',
                'plugin_config' => [
                    'valueField' => 'id',
                    'labelField' => 'name',
                    'searchField' => 'name',
                    'sortField' => 'name',
                    'create' => 0,
                    'maxItems' => 99999,
                    'plugins' => ['remove_button'],
                    'ajax_action' => 'searchProductByName'
                ],
                'operators' => [
                    'in',
                    'not_in'
                ],
            ],
            //NOTE MAIN
            /*			'seller.id_seller' => [
                            'field' => 'seller.id_seller',
                            'label' => $this->l('Sellers'),
                            'type' => 'integer',
                            'input' => 'select',
                            'multiple' => 1,
                            'operators' => [
                                'equal',
                                'not_equal',
                                'in',
                                'not_in'
                            ],
                            'values' => json_encode(
                                array_map(
                                    function ($seller) {
                                        return [
                                            'label' => $seller['shop_name'],
                                            'value' => $seller['id_seller']
                                        ];
                                    },
                                    Ets_mp_seller::_getSellers('and s.active = 1', '', '', PHP_INT_MAX)
                                )
                            )
                        ],
            */
            'category.id_category' => [
                'field' => 'category.id_category',
                'label' => $this->l('Categories'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => 1,
                'operators' => [
                    'in',
                    'not_in'
                ],
                'values' => json_encode(
                    array_map(
                        function ($category) {
                            return [
                                'label' => $category['name'],
                                'value' => $category['id_category']
                            ];
                        },
                        Category::getAllCategoriesName()
                    )
                )
            ],
            'product.price' => [
                'field' => 'product.price',
                'label' => $this->l('Product Price'),
                'type' => 'double',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 0.01
                    ]
                )
            ],
            'product.is_virtual' => [
                'field' => 'product.is_virtual',
                'label' => $this->l('Product Virtual'),
                'type' => 'integer',
                'input' => 'radio',
                'values' => json_encode(
                    [
                        '1' => $this->l('Yes'),
                        '0' => $this->l('No')
                    ]
                ),
                'operators' => [
                    'equal',
                ]
            ],
            'product.date_add' => [
                'field' => 'product.date_add',
                'label' => $this->l('Product Date Add'),
                'type' => 'date',
                'plugin' => 'datepicker',
                'plugin_config' => [
                    'dateFormat' => 'yy-mm-dd'
                ],
                'operators' => [
                    'equal',
                    'not_equal',
                    'between',
                    'not_between',
                    'less',
                    'greater',

                ]
            ],
            'product.id_supplier' => [
                'field' => 'product.id_supplier',
                'label' => $this->l('Supplier'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => 1,
                'operators' => [
                    'in',
                    'not_in'
                ],
                'values' => json_encode(
                    array_map(
                        function ($supplier) {
                            return [
                                'label' => $supplier['name'],
                                'value' => $supplier['id_supplier']
                            ];
                        },
                        Supplier::getSuppliers()
                    )
                )
            ],
            'product.id_manufacturer' => [
                'field' => 'product.id_manufacturer',
                'label' => $this->l('Brand'),
                'type' => 'integer',
                'input' => 'select',
                'multiple' => 1,
                'operators' => [
                    'in',
                    'not_in'
                ],
                'values' => json_encode(
                    array_map(
                        function ($manufacturer) {
                            return [
                                'label' => $manufacturer['name'],
                                'value' => $manufacturer['id']
                            ];
                        },
                        Manufacturer::getLiteManufacturersList()
                    )
                )
            ],
            'stock_available.quantity' => [
                'field' => 'stock_available.quantity',
                'label' => $this->l('Product Stock Quantity'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.additional_shipping_cost' => [
                'field' => 'product.additional_shipping_cost',
                'label' => $this->l('Product Additional Shipping Cost'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.width' => [
                'field' => 'product.width',
                'label' => $this->l('Product Width'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.height' => [
                'field' => 'product.height',
                'label' => $this->l('Product Height'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.depth' => [
                'field' => 'product.depth',
                'label' => $this->l('Product Depth'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.weight' => [
                'field' => 'product.weight',
                'label' => $this->l('Product Weight'),
                'type' => 'integer',
                'operators' => [
                    'equal',
                    'not_equal',
                    'less',
                    'less_or_equal',
                    'greater',
                    'greater_or_equal',
                    'between',
                    'not_between'
                ],
                'validation' => json_encode(
                    [
                        'min' => 0,
                        'step' => 1
                    ]
                )
            ],
            'product.available_date' => [
                'field' => 'product.available_date',
                'label' => $this->l('Product Available Date'),
                'type' => 'date',
                'plugin' => 'datepicker',
                'plugin_config' => [
                    'dateFormat' => 'yy-mm-dd'
                ],
                'operators' => [
                    'equal',
                    'not_equal',
                    'between',
                    'not_between',
                    'less',
                    'greater',

                ]
            ]
        ];
    }

    private function _getQueryBuilderWhere(): string
    {
        return '';
    }

    /** @UTILS */
    private function _queryJoinToString($query_joins): string
    {
        $join_string = '';
        foreach ($query_joins as $key => $joins) {
            foreach ($joins as $join) {
                $join_string .= $join['join_type'] . ' JOIN ' . $join['table'] . ' AS ' . $join['as'] . ' ON ' . implode(
                        ' AND ',
                        array_map(
                            function ($on_primary, $on_foreign) {
                                return $on_foreign . '=' . $on_primary;
                            },
                            $join['on'],
                            array_keys($join['on'])
                        )
                    ) . " \n";
            }
        }

        return $join_string;
    }

}