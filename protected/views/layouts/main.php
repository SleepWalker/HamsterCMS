<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="/css/normalize.min.css">
        <link rel="stylesheet" href="/css/style.css">

        <script src="/js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">Вы используете устаревший браузер. <a href="http://browsehappy.com/">Обновите свой браузер сегодня</a> или <a href="http://www.google.com/chromeframe/?redirect=true">установите Google Chrome Frame</a> что бы улучшить отображение этого сайта.</p>
        <![endif]-->

        <div class="header-container">
            <header class="wrapper clearfix">
                <a href="<?php echo Yii::app()->createUrl('page/index') ?>"><img src="/images/pwnz_logo.png" alt="PWN-Zone.com" /></a>
                <nav id="main">
                  <?php
                    Yii::app()->menuMap->render(array(
                      'О КОМПАНИИ' => '#',
                      'ПРОЕКТЫ' => '#',
                      'ПАРТНЕРСТВО' => '#',
                      'НОВОСТИ' => '#',
                      'КОНТАКТЫ' => array('site/contact'),
                    ));
                  ?>
                </nav>
                <div class="blackBlock ddmenu" id="dd_0">
                  <section>
                  <?php
                    Yii::app()->menuMap->render(array(
                      'Миссия' => array('page/index', 'path' => 'mission'),
                      'Наша команда' => array('page/index', 'path' => 'team'),
                      'Партнеры' => array('page/index', 'path' => 'partner'),
                      'Галерея' => array('photo/photo/index'),
                    ));
                  ?>
                  </section>
                </div><!-- О компании -->

                <div class="blackBlock ddmenu" id="dd_1">
                  <section>
                  <?php
                    Yii::app()->menuMap->render(array(
                      'Интернет магазин' => 'http://shop.pwn-zone.com',
                      'Веб-студия' => array('page/index', 'path' => 'webstudio'),
                      'Комплексный аутсорсинг' => array('page/index', 'path' => 'outsourcing'),
                      'Разработка приложений' => array('page/index', 'path' => 'development'),
                      'Облачные сервисы' => array('page/index', 'path' => 'cloud_computing'),
                    ));
                  ?>
                  </section>
                </div><!-- Проекты -->
                
                <div class="blackBlock ddmenu" id="dd_2">
                  <section>
                  <?php
                    Yii::app()->menuMap->render(array(
                      'Наши партнеры' => array('page/index', 'path' => 'partners'),
                      'Клиентам' => array('page/index', 'path' => 'for_clients'),
                      'Инвесторам' => array('page/index', 'path' => 'for_investors'),
                      'Стартапам' => array('page/index', 'path' => 'for_startups'),
                      'Вакансии' => array('page/index', 'path' => 'vacancies'),
                    ));
                  ?>
                  </section>
                </div><!-- Партнерство -->
                
                <div class="blackBlock ddmenu" id="dd_3">
                  <section>
                  <?php
                    Yii::app()->menuMap->render(array(
                      'Публикации' => array('blog/blog/index'),
                      'Мероприятия' => array('event/event/index'),
                      'Пресса о нас' => array('page/index', 'path' => 'presse'),
                    ));
                  ?>
                  </section>
                </div><!-- Новости -->
            </header>
        </div>

        <div class="main-container">
            <div class="main wrapper clearfix">
                <?php 
                  if($this->id == 'page' && empty($_GET['path'])) {
                ?>
                <section class="left tightC">
                  <?php $this->widget('blog.widgets.posts.Posts', array('cols'=> 3)); ?>
                </section>
                <aside class="right wideC">
                  <?php $this->widget('event.widgets.events.Events'); ?>
                </aside>
                <?php
                    }else{
                      echo $content;
                    }// endif($this->action->id != 'page') 
                  ?>

            </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <nav id="sociality">
                  <a href="https://www.facebook.com/pwnzonecom" class="FaceBook">FaceBook</a>
                  <a href="https://twitter.com/ChipShout" class="Twitter">Twitter</a>
                  <a href="http://www.linkedin.com/company/2766599" class="LinkedIn">LinkedIn</a>
                  <a href="/news/rss" class="RSS">RSS</a>
                </nav>
                2012 © PWN-Zone.com
                <div class="footer_aside"><!-- Контейнер, что бы избежать смещений копирайта -->
                <a href="<?php echo I18n::createUrl('en') ?>">en</a> 
                 <a href="<?php echo I18n::createUrl('ru') ?>">ru</a> 
                  <section id="lang">
                    <a href="" class="ru">RU</a>
                  </section>
                  <section id="search">
                    <button type="submit">Искать</button>
                    <input type="search" name="q" placeholder="Поиск..." />
                  </section>
                </div>
            </footer>
        </div>
    </body>
</html>
<?php
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl."/js/main.js",CClientScript::POS_END);
?>
