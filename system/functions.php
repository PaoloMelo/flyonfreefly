<?php

function __autoload($classe)
{
	$arquivo = $classe . '.php';
	$diretorios = [_DIR_CONTROLLERS_, _DIR_MODELS_, _DIR_VIEWS_, _DIR_HELPERS_];

	foreach ($diretorios as $d) {
		if (file_exists($d . $arquivo)) {
			require_once $d . $arquivo;
			return;
		}
	}

	die('Classe nao encontrada');
}

function printa($v, $dump = 0)
{
	echo '<pre>';
	if ($dump) {
		var_dump($v);
	} else {
		print_r($v);
	}
	echo '</pre>';
}

function getGravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array()) {
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}
