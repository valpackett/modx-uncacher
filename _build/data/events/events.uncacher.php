<?php

$events = array();

$events['OnDocFormSave'] = $modx->newObject('modPluginEvent');
$events['OnDocFormSave']->fromArray(array(
  'event' => 'OnDocFormSave',
  'priority' => 0,
  'propertyset' => 0,
), '', true, true);

return $events;
