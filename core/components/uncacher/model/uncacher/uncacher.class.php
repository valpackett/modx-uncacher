<?php
class Uncacher {
  public $modx;
  public $config = array();

  function __construct(modX &$modx, array $config = array()) {
    $this->modx = &$modx;

    $basePath = $this->modx->getOption('uncacher.core_path', $config, $this->modx->getOption('core_path').'components/uncacher/');
    $this->config = array_merge(array(
      'basePath' => $basePath,
      'corePath' => $basePath,
      'modelPath' => $basePath.'model/',
      'processorsPath' => $basePath.'processors/',
      'templatesPath' => $basePath.'templates/',
      'chunksPath' => $basePath.'elements/chunks/',
    ), $config);
    $this->modx->addPackage('uncacher', $this->config['modelPath']);
  }

  // getParentIds doesn't work on new resources, WTF
  // couldn't they do this when there's no URL map?!
  private function add_parent(&$ids, &$res, &$modx) {
    $par = $res->get('parent');
    if ($par) {
      array_push($ids, $par);
      $par_res = $modx->getObject('modResource', $par);
      $this->add_parent($ids, $par_res, $modx);
    }
  }

  function uncache($res, $recache) {
    $ids = array();

    array_push($ids, $res->get('id')); // current doc
    $this->add_parent($ids, $res, $this->modx);
    array_push($ids, $this->modx->getOption('site_start'));

    // Clear the URL map
    $query = $this->modx->newQuery('modContext');
    $query->select($this->modx->escape('key'));
    if ($query->prepare() && $query->stmt->execute()) {
      $contexts = $query->stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
      $contexts = array('web');
      $this->modx->log(modX::LOG_LEVEL_ERROR, 'Couldn\'t fetch contexts!');
    }
    $this->modx->cacheManager->refresh(array(
      'context_settings' => array('contexts' => $contexts)
    ));

    $this->modx->log(modX::LOG_LEVEL_INFO, 'Uncacher uncaching resources: '.implode(', ', $ids));

    // Re-cache resources
    foreach($ids as $id) {
      unlink(MODX_CORE_PATH.'cache/resource/web/resources/'.$id.'.cache.php');
      if ($recache == true) {
        file_get_contents($this->modx->makeUrl($id, '', '', 'full'));
      }
    }
  }

  function uncacheRecent($minutes, $recache) {
    $resources = $this->modx->getIterator('modResource', array(
      'pub_date:>=' => strtotime('now - '.$minutes.' minutes - 5 seconds'),
      'pub_date:<' => strtotime('now + 5 seconds'),
    ));

    foreach ($resources as $idx => $res) {
      $res->set('publishedon', $res->get('pub_date'));
      $res->set('pub_date', '');
      $res->set('published', 1);
      $res->save();
      $this->uncache($res, $recache);
    }
  }
}
