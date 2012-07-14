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

  // getParentresources doesn't work on new resources, WTF
  // couldn't they do this when there's no URL map?!
  private function add_parent(&$resources, &$res, &$modx) {
    $par = $res->get('parent');
    if ($par) {
      $par_res = $modx->getObject('modResource', $par);
      array_push($resources, $par_res);
      $this->add_parent($resources, $par_res, $modx);
    }
  }

  function uncache(&$res, $recache) {
    $resources = array();

    array_push($resources, $res);
    $this->add_parent($resources, $res, $this->modx);
    array_push($resources, $this->modx->getObject('modResource', $this->modx->getOption('site_start')));

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


    // Re-cache resources
    foreach ($resources as $res) {
      $path = MODX_CORE_PATH.'cache/resource/'.$res->get('context_key').'/resources/'.$res->get('id');
      $fpath = $path.'.cache.php'; // TODO: support json and serialized cache? who ever uses it?

      if (is_dir($path)) {
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Uncacher deleting '.$path);
        $this->modx->cacheManager->deleteTree($path, array('deleteTop' => true));
      }
      if (is_file($fpath)) {
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Uncacher deleting '.$fpath);
        unlink($fpath);
      }
      if ($recache == true) {
        file_get_contents($this->modx->makeUrl($res->get('id'), '', '', 'full'));
      }
    }

    return true;
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

    return true;
  }
}
