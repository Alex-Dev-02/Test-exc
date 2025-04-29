<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Uri;

global $USER, $APPLICATION;

$general = [
    [
        'BITRIX_24_WEBHOOK_URL',
        Loc::getMessage('ALEX_FORM_B24_WEBHOOK_URL'),
        '',
        ['text', 50]
    ],
    [
        'TARGET_FORM_ID',
        Loc::getMessage('ALEX_FORM_TARGET_FORM_ID'),
        '',
        ['text', 10]
    ]
];

$tabs = [
    [
        'DIV' => 'settings',
        'TAB' => Loc::getMessage('ALEX_FORM_SETTINGS_TAB'),
        'TITLE' => Loc::getMessage('ALEX_FORM_SETTINGS_TITLE'),
    ],
];

if ($USER->IsAdmin() && check_bitrix_sessid() && !empty($_POST['save'])) {
    foreach ($general as $option) {
        __AdmSettingsSaveOptions($mid, [$option]);
    }
    LocalRedirect($APPLICATION->GetCurPageParam());
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin();
?>
<form method="POST" action="<?= HtmlFilter::encode((string)(new Uri(Context::getCurrent()->getRequest()->getRequestUri()))->addParams(['mid' => $mid, 'lang' => LANGUAGE_ID])); ?>">
    <?php $tabControl->BeginNextTab(); ?>
    <?php __AdmSettingsDrawList($mid, $general); ?>
    <?php $tabControl->Buttons(['btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false]); ?>
    <?= bitrix_sessid_post(); ?>
    <?php $tabControl->End(); ?>
</form>