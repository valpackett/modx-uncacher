<?php

$modxpath = getenv('MODX_PATH');

if ($modxpath == false) {
  $modxpath = getenv('HOME').'/Sites';
}

require_once '/'.trim($modxpath, '/').'/config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
