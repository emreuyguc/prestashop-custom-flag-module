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

if (!defined('_PS_VERSION_')) exit;

use Db;
use Tools;
use Context;

class DbModel extends \ObjectModel {

	public $errors;

	protected const TYPE_TEXT = 'text';
	protected const TYPE_MEDIUMTEXT = 'mediumtext';
	protected const TYPE_ENUM = 'enum';

	protected static $_USE_PREFIX = TRUE;

	//todo raw select kodları için multilang
	//todo : toggleStatus()
	//todo : instance check table and schema

	public static function getTableName(): string {
		return (isset(static::$_USE_PREFIX) && static::$_USE_PREFIX == TRUE ? _DB_PREFIX_ . static::$definition['table'] : static::$definition['table']);
	}

	private static function _typeToSql(string $type): string {
		switch ($type) {
			case static::TYPE_INT:
				return 'int';
			case static::TYPE_STRING:
				return 'varchar';
			case static::TYPE_FLOAT:
				return 'float';
			case static::TYPE_DATE:
				return 'datetime';
			case static::TYPE_BOOL:
				return 'tinyint(1)';
			case static::TYPE_TEXT;
				return 'text';
			case static::TYPE_MEDIUMTEXT;
				return 'mediumtext';
			case static::TYPE_ENUM:
				return 'enum';
		}
	}

	private static function _makeColumnSql(array $fields): string {
		$col_sql = '';
		foreach ($fields as $col_name => $col_info) {
			$col_sql .= "`{$col_name}` " .
						static::_typeToSql(
							$col_info['type']
						) .
						(isset($col_info['size']) ? ($col_info['type'] == static::TYPE_BOOL ? '' : "({$col_info['size']})") : '') .
						(isset($col_info['enums']) ? '(\'' . implode(
								'\',\'',
								$col_info['enums']
							) . '\')' : '') .
						(isset($col_info['required']) && $col_info['required'] == TRUE ? ' NOT NULL ' : '') .
						(isset($col_info['auto_date']) ? ($col_info['auto_date'] == 'insert' ? ' DEFAULT CURRENT_TIMESTAMP ' : ' ON UPDATE CURRENT_TIMESTAMP ') : '') .
						',';
		}

		return $col_sql;
	}

	private static function _makecolIndexFromDefination(): string {
		$index_sql = [];
		foreach (static::$definition['fields'] as $col_name => $col_info) {
			if (isset($col_info['uniq']) && $col_info['uniq'] == TRUE) {
				$index_sql[] = ",UNIQUE (`{$col_name}`)";
			}
		}
		$index_sql = implode('', $index_sql);

		return $index_sql;
	}

	public static function createTable() {
		$tableName = static::$definition['table'];
		$primaryField = static::$definition['primary'];
		$fields = static::$definition['fields'];
		if(isset(static::$definition['multilang']) && static::$definition['multilang'] == TRUE){
			$multilang_fields = [
				'id_lang' => [
					'type' => self::TYPE_INT,
					'unsigned' => TRUE,
					'required' => TRUE,
					'size' => 10
				]
			];
			foreach ($fields as $field => $field_info) {
				if (isset($field_info['lang']) && $field_info['lang'] == TRUE) {
					$multilang_fields[$field] = $fields[$field];
					unset($fields[$field]);
				}
			}
		}

		$sqls[] = '  			CREATE TABLE IF NOT EXISTS `' . (isset(static::$_USE_PREFIX) && static::$_USE_PREFIX == TRUE ? _DB_PREFIX_ . $tableName : $tableName) . '` (
  				`' . $primaryField . '` int(10) unsigned NOT NULL AUTO_INCREMENT,
  				' . static::_makeColumnSql($fields) . '				
  				PRIMARY KEY (`' . $primaryField . '`)  				
  				' . static::_makecolIndexFromDefination() . ' 			
  				) ENGINE=InnoDB DEFAULT CHARSET=utf8;  		';

		if (isset(static::$definition['multilang']) && static::$definition['multilang'] == TRUE) {
			$sqls[] = '  			CREATE TABLE IF NOT EXISTS `' . (isset(static::$_USE_PREFIX) && static::$_USE_PREFIX == TRUE ? _DB_PREFIX_ . $tableName : $tableName) . '_lang` (
  				`' . $primaryField . '` int(10) unsigned NOT NULL AUTO_INCREMENT,
  				' . static::_makeColumnSql($multilang_fields) . '				
  				PRIMARY KEY (`' . $primaryField . '` , `id_lang`)  				 			
  				) ENGINE=InnoDB DEFAULT CHARSET=utf8;  		';
		}

        $executes = [];
        foreach ($sqls as $sql){
            $executes[] = Db::getInstance()->execute($sql);
        }

		return !in_array(false,$executes);
	}

	public static function selectInit(string $where, $order_by = NULL): self {
		$id = Db::getInstance()
				->getValue(
					'SELECT ' .
					static::$definition['primary'] .
					' FROM ' .
					static::getTableName() .
					(!is_null($where) ? " WHERE {$where}" : '') .
					(!is_null($order_by) ? ' ORDER BY ' . implode(
							',',
							array_map(
								function ($column) {
									return implode(' ', $column);
								},
								$order_by
							)
						) : '')
				);

		return new static($id);
	}

	public static function select(array $columns = NULL, string $where = NULL, string $join = NULL, array $order_by = NULL, int $limit = NULL, int $offset = NULL): array {
		return (Db::getInstance()
				  ->executeS(
					  'SELECT ' .
					  ($columns ? implode(
						  ',',
						  array_map(
							  function ($column) {
								  if (is_array($column)) {
									  return $column['query'] . ' as ' . '`' . $column['as'] . '`';
								  }

								  return '`' . $column . '`';
							  },
							  $columns
						  )
					  ) : '*') .
					  ' FROM ' .
					  static::getTableName() .
					  ' as `A` ' .
					  (!is_null($join) ? " {$join}" : '') .
					  (!is_null($where) ? " WHERE {$where}" : '') .
					  (!is_null($order_by) ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '') .
					  (!is_null($limit) ? ' LIMIT ' . $limit . (!is_null($offset) ? ',' . $offset : '') : '')
				  ));
	}

	public static function selectRow(string $where = NULL, array $select_columns = NULL, array $order_by = NULL): array {
		return (Db::getInstance()
				  ->getRow(
					  'SELECT ' .
					  ($select_columns ? '`' . implode('`,`', $select_columns) . '`' : '*') .
					  ' FROM ' .
					  static::getTableName() .
					  (!is_null($where) ? " WHERE {$where}" : '') .
					  (!is_null($order_by) ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '')
				  ));
	}

	public static function selectValue(string $select_column, string $where = NULL, array $order_by = NULL): ?string {
		return (Db::getInstance()
				  ->getValue(
					  'SELECT ' . $select_column . ' FROM ' . static::getTableName() . (!is_null($where) ? " WHERE {$where}" : '') . (!is_null($order_by) ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '')
				  ));
	}

	//note is
	public static function check(string $where): bool {
		return (Db::getInstance()
				  ->getValue('SELECT COUNT(*) FROM ' . static::getTableName() . " WHERE {$where}"));
	}

	public static function deleteRow(string $where): bool {
		return (Db::getInstance()
				  ->delete(static::$definition['table'], $where, 0, FALSE, static::$_USE_PREFIX));
	}

	public static function insertRow(array $fields, $insert_type = Db::INSERT): bool {
		return (Db::getInstance()
				  ->insert(
					  static::$definition['table'],
					  $fields,
					  TRUE,
					  FALSE,
					  $insert_type,
					  static::$_USE_PREFIX
				  ));
	}

	public static function updateRow(array $fields, string $where): bool {
		return (Db::getInstance()
				  ->update(
					  static::$definition['table'],
					  $fields,
					  $where,
					  0,
					  TRUE,
					  FALSE,
					  static::$_USE_PREFIX
				  ));
	}


	public function toggleColumn(string $column) {
		$this->{$column} = !$this->{$column};
		return $this->save();
	}

	public static function toggleRowColumn($column,$id):bool{
	}

}