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

#REQUIRED DEFAULTS
require_once 'configs/ModuleConfigs.php';
require_once 'controllers/admin/ModuleConfiguration.php';
require_once 'hooks/ModuleHooks.php';

#INCLUDE HELPERS
require_once 'helpers/DbModelHelper.php';
require_once 'helpers/DbModelFormHelper.php';
require_once 'helpers/DbModelListHelper.php';

#INCLUDE TYPES *INTERFACE

#INCLUDE UTILITY *GLOBAL,TRAIT,CLASS
require_once 'utils/DebugUtility.php';
require_once 'utils/GlobalUtility.php';

#INCLUDE MODELS *CLASS
require_once 'models/DbModels/CustomFlag.php';
require_once 'models/DbModels/FlagGroup.php';
require_once 'models/DbModels/GroupFlag.php';
require_once 'models/DbModels/GroupProduct.php';

#INCLUDE CONFIGS *INTERFACE

#INCLUDE APP *CUSTOM APP FOLDER
require_once 'app/FlagBuilder.php';

#INCLUDE CONTROLLERS *CLASS

#INCLUDE HOOKS *TRAIT


class euu_customflag extends Module implements euu_customflag\Configs\ModuleConfigs {

    #REQUIRED
    use euu_customflag\ModuleConfigurationController;
    use euu_customflag\Hooks\ModuleHooks;
    use euu_customflag\Utils\DebugUtility;

    #USE HOOKS START

    #USE HOOKS USE END


    public function __construct() {
        $this->_callModuleHook('_beforeConstruct');
        $this->name = 'euu_customflag';
        $this->version = '1.0.0';
        $this->displayName =  'EUU - PRODUCT CUSTOM FLAGS';
        $this->description = 'Product dynamic custom flags.';
        $this->tab = 'front_office_features';
        $this->author = 'emreuyguc';

        $this->need_instance = self::NEED_INSTANCE;
        $this->bootstrap = self::USE_BOOTSTRAP;
        $this->confirmUninstall = $this->l(self::UNINSTALL_MSG);
        $this->controllers = self::CONTROLLERS;
        $this->ps_versions_compliancy = array('min' => self::MIN_PS_VERSION, 'max' => self::MAX_PS_VERSION);
        parent::__construct();

        $this->_callModuleHook('_afterConstruct');
    }

    public function postProcess() {
        //todo chek here for ajax
        $this->ajax = FALSE;
        if (Tools::getValue('ajax')) {
            $this->ajax = TRUE;
        } else {
            if(Tools::getIsset('action')){
                $callback_prefix = 'process';
                $action = Tools::toCamelCase(Tools::getValue('action'), TRUE);
                if (method_exists($this, $callback_prefix . $action)) {
                    $this->{$callback_prefix . $action}();
                }
            }
        }

        /*
         else{
            foreach (Tools::getAllValues() as $post_key => $post_value) {
                $method_name = $callback_prefix . ucfirst($post_key);
                if (method_exists($this, $method_name)) {
                    $this->$method_name();
                }
            }
        }
         */
    }

    public function getContent() {
        $this->postProcess();

        if ($this->ajax) {
            $callback_prefix = 'displayAjax';
        } else {
            $callback_prefix = 'displayConfig';
        }

        if (method_exists($this, $callback_prefix)) {
            return $this->{$callback_prefix}();
        }
        return false;
    }

    public function enable($force_all = FALSE) {
        return parent::enable($force_all) && $this->_installTabs();
    }

    public function disable($force_all = FALSE) {
        return parent::disable($force_all) && $this->_uninstallTabs();
    }

    public function install() {
        return $this->_callModuleHook('_beforeInstall') && $this->_execSqls('install') && parent::install() && $this->_installTabs() && $this->_registerHooks() && $this->_initDefaultConfigurationValues() && $this->_callModuleHook('_afterInstall');
    }

    public function uninstall() {
        return $this->_callModuleHook('_beforeUninstall') && $this->_execSqls('uninstall') && parent::uninstall() && $this->_uninstallTabs() && $this->_unregisterHooks() && $this->_deleteConfigurationValues()  && $this->_callModuleHook('_afterUninstall');
    }

    private function _callModuleHook($hook) {
        if (method_exists($this, $hook)) {
            $this->{$hook}();
        }

        return TRUE;
    }

    private function _registerHooks() {
        foreach (self::USE_HOOKS as $hook) {
            $this->registerHook($hook);
        }

        return TRUE;
    }

    private function _unregisterHooks() {
        foreach (self::USE_HOOKS as $hook) {
            $this->unregisterHook($hook);
        }

        return TRUE;
    }

    private function _initDefaultConfigurationValues() {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            $db_key = $this->name . '_' . $key;
            if (self::DEFAULT_CONFIGURATION[$key] != Configuration::get($db_key)) {
                Configuration::updateValue($db_key, $value);
            }
        }

        return TRUE;
    }

    private function _deleteConfigurationValues() {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            $db_key = $this->name . '_' . $key;
            Configuration::deleteByName($db_key);
        }

        return TRUE;
    }

    private function _installTabs($tabs = NULL, $parent_tab_id = 0) {
        $tabs = $tabs ?? self::TABS;
        foreach ($tabs as $index => $defined_tab) {

            $id_tab = (int)Tab::getIdFromClassName($defined_tab['controller']);
            if (!$id_tab) {
                $id_tab = NULL;
            }
            $tab = new Tab($id_tab);
            $tab->class_name = $defined_tab['controller'];
            foreach (Language::getLanguages() as $lang) {
                if(is_array($defined_tab['title'])){
                    if(array_key_exists($lang['iso_code'],$defined_tab['title'])){
                        $tab->name[$lang['id_lang']] = $defined_tab['title'][$lang['iso_code']];
                    }else{
                        $tab->name[$lang['id_lang']] = $defined_tab['title'][array_keys($defined_tab['title'])[0]];
                    }
                }else{
                    $tab->name[$lang['id_lang']] = $defined_tab['title'];
                }
            }
            $tab->id_parent = isset($defined_tab['parent_tab']) ? (int)Tab::getIdFromClassName(
                $defined_tab['parent_tab']
            ) : $parent_tab_id;
            $tab->position = $defined_tab['position'] ?? Tab::getNewLastPosition($tab->id_parent);
            $tab->icon = $defined_tab['icon'] ?? NULL;
            $tab->active = 1;
            $tab->module = $this->name;
            try {
                $tab->save();
            } catch (Exception $e) {
                $this->_errors[] = $e->getMessage();
                if (isset($defined_tab['tabs'])) {
                    break;
                }
            }
            if (isset($defined_tab['tabs'])) {
                $this->_installTabs($defined_tab['tabs'], $tab->id);
            }
        }
        if (count($this->_errors) > 0) {
            return FALSE;
        }

        return TRUE;
    }

    private function _uninstallTabs($tabs = NULL) {
        $tabs = $tabs ?? self::TABS;
        foreach ($tabs as $index => $defined_tab) {
            $id_tab = (int)Tab::getIdFromClassName($defined_tab['controller']);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                try {
                    $tab->delete();
                } catch (Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
            if (isset($defined_tab['tabs'])) {
                $this->_uninstallTabs($defined_tab['tabs']);
            }
        }
        if (count($this->_errors) > 0) {
            return FALSE;
        }

        return TRUE;
    }

    private function _execSqls($dir){
        $dir = __DIR__.'/sql/'.$dir.'/';
        if(!file_exists($dir)) return true;

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file){
            if( !$this->_execSqlFile($dir.$file) ){
                return FALSE;
            }
        }
        return TRUE;
    }

    private  function _execSqlFile($file_path){
        $sql = str_replace('PREFIX_',_DB_PREFIX_,file_get_contents($file_path));
        return !$sql || Db::getInstance()->execute($sql);
    }

    public function viewAccess($disable=false){
        return true;
    }

}