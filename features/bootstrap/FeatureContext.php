<?php

assert_options( ASSERT_CALLBACK, 'assert_callback');

function assert_callback($script, $line, $message) {
  throw new Exception('Failed assertion at '.$script.':'.$line.': '.$message);
}

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Exception\PendingException;
/* use Behat\Gherkin\Node\PyStringNode, */
/*     Behat\Gherkin\Node\TableNode; */

require_once dirname(dirname(dirname(__FILE__))).'/require_modx.php';

class FeatureContext extends BehatContext {
  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    $this->modx = new modX();
    $this->modx->initialize('web');
    $this->modx->getService('error', 'error.modError');

    $this->modx->setOption('uncacher.core_path', getcwd().'/core/components/uncacher/');
    $this->uncacher = $this->modx->getService('uncacher', 'Uncacher', $this->modx->getOption('uncacher.core_path', null).'model/uncacher/');
    if (!($this->uncacher instanceof Uncacher)) throw new Exception('Uncacher not loaded');

    $tpl = $this->modx->newObject('modTemplate');
    $tpl->set('templatename', 'CacheTemplate');
    $tpl->set('content', '[[*pagetitle]] from [^s^]');
    $tpl->save();
    // Saved tpl doesn't get an id, have to re-fetch :-(
    $this->tpl_id = $this->modx->getObject('modTemplate', array('templatename' => 'CacheTemplate'))->get('id');

    $this->res_ids = array();
  }

  private function getResource($pagetitle) {
    return $this->modx->getObject('modResource', array('pagetitle' => $pagetitle));
  }

  private function makeUrl($id) {
    return $this->modx->makeUrl($id, '', '', 'full');
  }

  private function saveResource(&$res, $pagetitle) {
    $res->fromArray(array(
      'pagetitle' => $pagetitle,
      'published' => 1,
      'template'  => $this->tpl_id
    ));
    $res->save();
    array_push($this->res_ids, $res->get('id'));

    $url = $this->makeUrl($res->get('id'));
    file_get_contents($url);
  }

  /** @Given /^I have a cached resource named "([^"]*)"$/ */
  public function iHaveACachedResourceNamed($pagetitle) {
    $res = $this->modx->newObject('modDocument');
    $this->saveResource($res, $pagetitle);
  }

  /** @Given /^I have a cached resource named "([^"]*)" with pub_date "([^"]*)"$/ */
  public function iHaveACachedResourceNamedWithPubdate($pagetitle, $pub_date) {
    $res = $this->modx->newObject('modDocument');
    $res->set('pub_date', strtotime($pub_date));
    $this->saveResource($res, $pagetitle);
  }

  /** @Given /^I have a cached resource named "([^"]*)" under "([^"]*)"$/ */
  public function iHaveACachedResourceNamedUnder($pagetitle, $parent) {
    $res = $this->modx->newObject('modDocument');
    $res->set('parent', $this->getResource($parent)->get('id'));
    $this->saveResource($res, $pagetitle);
  }

  /** @When /^I clear the cache of "([^"]*)"$/ */
  public function iClearTheCacheOf($pagetitle) {
    $res = $this->getResource($pagetitle);
    $this->uncacher->uncache($res, false);
  }

  /** @When /^I clear the cache of resources published in "([^"]*)" minutes$/ */
  public function iClearTheCacheOfResourcesPublishedIn($minutes) {
    $this->uncacher->uncacheRecent($minutes, false);
  }

  /** @Then /^resource "([^"]*)" is not cached$/ */
  public function resourceIsNotCached($pagetitle) {
    $url = $this->makeUrl($this->getResource($pagetitle)->get('id'));
    assert(file_get_contents($url) == $pagetitle.' from database');
  }

  /** @Given /^resource "([^"]*)" is cached$/ */
  public function resourceIsCached($pagetitle) {
    $url = $this->makeUrl($this->getResource($pagetitle)->get('id'));
    assert(file_get_contents($url) == $pagetitle.' from cache');
  }

  /** @Then /^resource "([^"]*)" is published "([^"]*)"$/ */
  public function resourceIsPublished($pagetitle, $published) {
    $res = $this->getResource($pagetitle);
    // cutting off seconds with substr
    assert(substr(strtotime($res->get('publishedon')), 0, 9) == substr(strtotime($published), 0, 9));
  }

  public function __destruct() {
    $this->modx->getObject('modTemplate', $this->tpl_id)->remove();
    foreach($this->res_ids as $rid) {
      $this->modx->getObject('modResource', $rid)->remove();
    }
  }
}
