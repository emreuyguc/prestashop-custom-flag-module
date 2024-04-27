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

use Db;
use Context;

class FlagBuilder {
	private static $_instance;
	private $_flags;
	private $_product_flags;

	public static function getInstance(){
		if(!self::$_instance){
			self::$_instance = new self();
			self::$_instance->_initFlags();
		}
		return self::$_instance;
	}

	private function _initFlags(){

		$flags_query = Db::getInstance()
				   ->executeS(
					   '
			SELECT flag.*,flag_lang.* FROM `'._DB_PREFIX_.'euu_customflag_flags` flag
				INNER JOIN '._DB_PREFIX_.'euu_customflag_group_flags as gflag ON  flag.id_flag = gflag.id_flag and flag.is_active = 1
				INNER JOIN '._DB_PREFIX_.'euu_customflag_flag_groups as grp ON  gflag.id_flag_group = grp.id_flag_group and grp.is_active = 1 and 
				    (
                        (
                            (grp.date_start <= now() or grp.date_start = 0) 
                            and 
                            (grp.date_end >= now() or grp.date_end = 0)
                        )
                    )
				INNER JOIN '._DB_PREFIX_.'euu_customflag_flags_lang as flag_lang ON flag.id_flag = flag_lang.id_flag and flag_lang.id_lang = '.Context::getContext()->language->id.'

			GROUP BY flag.id_flag
		'
				   );

		foreach ($flags_query as $flag){
				$flag['img_url'] = _PS_IMG_ . 'euu_customflag'. '/FlagImages/'.$flag['img'];
				$this->_flags[$flag['id_flag']] = $flag;
		}

		$product_flags_query = Db::getInstance()
							 ->executeS(
								 '
		SELECT id_product , GROUP_CONCAT(flag.id_flag order by grflag.position,grflag.id_flag) as flags FROM `'._DB_PREFIX_.'euu_customflag_group_products` prod
			INNER JOIN '._DB_PREFIX_.'euu_customflag_group_flags grflag ON prod.id_flag_group = grflag.id_flag_group
			INNER JOIN '._DB_PREFIX_.'euu_customflag_flag_groups gr ON grflag.id_flag_group = gr.id_flag_group and gr.is_active = 1 and 
			        (
                        (
                            (gr.date_start <= now() or gr.date_start = 0) 
                            and 
                            (gr.date_end >= now() or gr.date_end = 0)
                        )
                    )
			INNER JOIN '._DB_PREFIX_.'euu_customflag_flags flag ON grflag.id_flag = flag.id_flag and flag.is_active = 1
		GROUP BY id_product 
			order by id_product'
							 );

		foreach ($product_flags_query as $product_flag) {
			$this->_product_flags[$product_flag['id_product']] = explode(',',$product_flag['flags']);
		}

	}


	public function getProductFlags($id_product){
		$flags = [];
		if(isset($this->_product_flags[$id_product])){
			foreach ( $this->_product_flags[$id_product] as $id_flag){
				$flags[] = $this->_flags[$id_flag];
			}
		}
		return $flags;
	}

	public function getFlags($id_flag = null){
		return $id_flag ? $this->_flags[$id_flag] : $this->_flags;
	}
}