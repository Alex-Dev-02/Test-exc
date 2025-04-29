<?php

use alex\form\lib\handlers\FormHandler;
use Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Класс Модуля alex.form
 */
class alex_form extends CModule
{
    public function __construct()
    {
        $moduleVersion = [];

        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $moduleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $moduleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage('FORM_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('FORM_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('FORM_PARTNER_NAME');
        $this->MODULE_ID = 'alex.form';
    }

    /**
     * @return void
     */
    function DoInstall(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);

        include_once __DIR__ . '/../include.php';

        $this->InstallEvents();
    }

    /**
     * @return void
     */
    public function InstallEvents(): void
    {
        RegisterModuleDependences(
            'form',
            'onAfterResultAdd',
            $this->MODULE_ID,
            'alex\form\FormHandler',
            'onAfterResultAdd'
        );
    }

    /**
     * @return void
     *
     * @throws ArgumentNullException
     */
    public function DoUninstall(): void
    {
        $this->UninstallEvents();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    /**
     * @return void
     */
    public function UninstallEvents(): void
    {
        UnRegisterModuleDependences(
            'form',
            'onAfterResultAdd',
            $this->MODULE_ID,
            'alex\form\FormHandler',
            'getFormData'
        );
    }
}
