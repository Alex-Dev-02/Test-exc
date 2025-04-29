<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('alex.form', [
    'alex\form\FormHandler' => 'lib/FormHandler.php',
    'alex\form\lib\services\B24Service' => 'lib/services/B24Service.php',
    'alex\form\lib\rest\B24Client' => 'lib/rest/B24Client.php',
    'alex\form\lib\FieldMapper' => 'lib/FieldMapper.php',
]);