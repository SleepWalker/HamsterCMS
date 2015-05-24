<!DOCTYPE HTML>
<html>
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="icon" type="image/gif" href="<?php echo $this->adminAssetsUrl; ?>/favicon.gif" />
    <link type="text/css" rel="StyleSheet" href="<?php echo $this->adminAssetsUrl; ?>/css/admin.css" />
    <title><?php echo strip_tags($this->pageTitle); ?></title>
</head>
<body>
    <div class="header_big" id="header">
        <div class="header_bg">
            <div id="menu">
                <a href="/" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_main.png" class="menu_top" alt="Главная" /></a>
                <a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_config.png" alt="Настройки" /></a>
                <a href="<?php echo \Yii::app()->user->logoutUrl; ?>" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_logout.png" alt="Выход из админ панели" /></a>
                <a href="<?php echo \Yii::app()->createUrl('admin/admin/index'); ?>"><img src="<?php echo $this->adminAssetsUrl; ?>/images/humsterLogo.png" id="logo" alt="Hamster CMS" /></a>
                <a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_modules.png" alt="Модули" /></a>
                <a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_extensions.png" alt="Расширения" /></a>
                <a href="/admin/tmpls" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_design.png" alt="Дизайн" /></a>
            </div>

            <div id="menu1" class="ddmenu">
                <?php
                    \Yii::app()->menuMap->render(array(
                        'Бекап' => array('/admin/backup'),
                        'Логи' => array('/admin/logs'),
                        'Настройки Hamster' => array('/admin/config'),
                        'Обновление Hamster' => array('/admin/update'),
                        'Тест' => array('/admin/test'),
                    ), 'hamsterConfig');
                ?>
            </div>

            <div id="menu4" class="ddmenu">
                <?php
                $modulesInfo = $this->modulesInfo;
                $enabledModules = $this->enabledModules;
                $menuArray['Управление страницами'] = array('admin/page');
                if (count($modulesInfo)) {
                    foreach ($modulesInfo as $moduleId => $moduleConfig) {
                        if (!array_key_exists($moduleId, $enabledModules)) {
                            continue; // модуль выключен
                        }

                        // Определяем в какое меню пойдет модуль в зависимости
                        // от того, есть ли у него контент (контент-модуль)
                        $menuVarName = isset($moduleConfig['internal']) ? 'extraMenuArray' : 'menuArray';
                        ${$menuVarName}[$moduleConfig['title']] = array('admin/' . $moduleId);
                    }
                }
                \Yii::app()->menuMap->render($menuArray, 'hamsterContentModules');
                ?>
            </div>

            <div id="menu5" class="ddmenu">
            <?php
            if (isset($extraMenuArray) && is_array($extraMenuArray)) {
                \Yii::app()->menuMap->render($extraMenuArray, 'hamsterInternalModules');
            }
            ?>
            </div>

            <div id="menu6" class="ddmenu">
            <?php //$content->printTmplsMenu(); ?>
            </div>

        </div>
        <div class="header_bottom"></div>
    </div>

    <div class="wrapper">
        <?php echo $content; ?>
    </div>
    <div id="footer">
    <div class="footer_line"></div>
    </div>
</body>
</html>
<?php
\Yii::app()->clientScript->registerCoreScript('jquery');
\Yii::app()->clientScript->registerScriptFile($this->adminAssetsUrl."/js/admin.js", CClientScript::POS_END);
