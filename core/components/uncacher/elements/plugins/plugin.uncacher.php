<?php

$uncacher = $modx->getService('uncacher', 'Uncacher', $modx->getOption('uncacher.core_path', null, $modx->getOption('core_path').'components/uncacher/').'model/uncacher/');
if (!($uncacher instanceof Uncacher)) return '';

$e = &$modx->event;
$res = $e->params['resource'];

$uncacher->uncache($res, true);
