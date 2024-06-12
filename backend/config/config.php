<?php

define('DBUSER', 'MartinezJuan');
define('DBPASS', 'FinalWebI');
define('DBBASE', 'FinalWebI');
define('DBHOST', 'localhost');

function sf__restablecerSql () {
	$sqls = array_map(function ($v) {return trim($v);}, explode(PHP_EOL, file_get_contents(__DIR__ . '/../adicional/bd_dump.sql')));
	$nuevoSql = implode(PHP_EOL, array_slice($sqls, array_search('-- #####CORTE#####', $sqls)));
	return $nuevoSql;
}