<?php
class Uncacher {
  public $modx;
  public $config = array();

  function __construct(modX &$modx, array $config = array()) {
    $this->modx = &$modx;

    $basePath = $this->modx->getOption('uncacher.core_path', $config, $this->modx->getOption('core_path').'components/doodles/');
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

  function uncache($res, $recache = false) {
    $ids = array();

    // getParentIds doesn't work on new resources, WTF
    // couldn't they do this when there's no URL map?!
    function add_parent(&$ids, $res, &$modx) {
      $par = $res->get('parent');
      if ($par) {
        array_push($ids, $par);
        $par_res = $modx->getObject('modResource', $par);
        add_parent($ids, $par_res, $modx);
      }
    }

    array_push($ids, $res->get('id')); // current doc
    add_parent($ids, $res, $this->modx);
    array_push($ids, 1); // index page TODO: non-1 index page

    // Clear the URL map
    $this->modx->cacheManager->refresh(array(
      // TODO: different contexts
      'context_settings' => array('contexts' => array('web'))
    ));

    // Re-cache resources
    foreach($ids as $id) {
      unlink(MODX_CORE_PATH.'cache/resource/web/resources/'.$id.'.cache.php');
      if ($recache) {
        file_get_contents($this->modx->makeUrl($id, '', '', 'full'));
      }
    }
  }
}
