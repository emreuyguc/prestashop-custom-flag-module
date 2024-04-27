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

namespace euu_customflag\Utils;

if (!defined('_PS_VERSION_')) exit;
function str_replace_limit(string $search, string $replace, string $subject, int $limit, &$count = NULL): string {
	$count = 0;
	if ($limit <= 0) return $subject;
	$occurrences = substr_count($subject, $search);
	if ($occurrences === 0) return $subject; else if ($occurrences <= $limit) return str_replace(
		$search,
		$replace,
		$subject,
		$count
	);
	//Do limited replace
	$position = 0;
	//Iterate through occurrences until we get to the last occurrence of $search we're going to replace
	for ($i = 0; $i < $limit; $i++) $position = strpos($subject, $search, $position) + strlen($search);
	$substring = substr($subject, 0, $position + 1);
	$substring = str_replace($search, $replace, $substring, $count);

	return substr_replace($subject, $substring, 0, $position + 1);
}

function sql_replace(string $sql, array $values) : string{
	for ($i = 0, $iMax = count($values); $i < $iMax; $i++) {
		$sql = str_replace_limit('?', !is_int($values[$i]) ? "'" . $values[$i] . "'" : $values[$i], $sql, 1);
	}

	return $sql;
}

function get_class_name($full_name){
	$path = explode('\\', $full_name);
	return array_pop($path);
}
