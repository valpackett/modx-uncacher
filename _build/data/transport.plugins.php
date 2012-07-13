<?php

function getPluginContent($filename) {
  $o = file_get_contents($filename);
  $o = trim(str_replace(array('<?php', '?>'), '', $o));
  return $o;
}

$plugins = array();

$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->fromArray(array(
  'id' => 1,
  'name' => 'uncacher',
  'description' => 'Clears the cache of saved resources, their parents and the index page',
  'plugincode' => getPluginContent($sources['elements'].'plugins/plugin.uncacher.php'),
  'category' => 0,
), '', true, true);

$events = include $sources['data'] . 'events/events.uncacher.php';

if (is_array($events) && !empty($events)) {
  $plugins[0]->addMany($events);
  $modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in '.count($events).' Plugin Events.');
} else {
  $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events.');
}

return $plugins;
