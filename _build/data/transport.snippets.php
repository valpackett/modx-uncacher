<?php

function getSnippetContent($filename) {
  $o = file_get_contents($filename);
  $o = trim(str_replace(array('<?php', '?>'), '', $o));
  return $o;
}

$snippets = array();

$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
  'id' => 1,
  'name' => 'uncacheRecent',
  'description' => 'Clears the cache of recently published resources, their parents and the index page. Execute with cron.',
  'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.uncacheRecent.php'),
  'category' => 0,
), '', true, true);

return $snippets;
