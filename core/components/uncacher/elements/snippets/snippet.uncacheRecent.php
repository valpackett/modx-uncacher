<?php

$uncacher = $modx->getService('uncacher', 'Uncacher', $modx->getOption('uncacher.core_path', null, $modx->getOption('core_path').'components/uncacher/').'model/uncacher/', $scriptProperties);
if (!($uncacher instanceof Uncacher)) return '';

$uncacher->uncacheRecent($minutes, true);
