<?php
$this->pageTitle = 'Прайсы';
$this->breadcrumbs = array($this->pageTitle);
?>

<h1><?=$title?></h1>

<?php
if (!empty($priceDownloadMenu)) {
    ?>
    <section class="priceDownload">
        <a href="" id="priceDownloadLink">Скачать прайсы</a>
        <ul>
        <?php
        foreach ($priceDownloadMenu as $item) {
            ?>
            <li><a href="<?=$item['link']?>"><?=$item['name']?></a></li>
            <?php
        }
        ?>
        </ul>
    </section>
<?php
}
?>

<?php Yii::app()->clientScript->registerScript(__FILE__ . '#скрипт для анимации и отображения прайсов', '
  $(function() {
    $(\'#priceDownloadLink\').click(function() {
      $(this).next().show(\'normal\');
      $(this).remove();

      return false;
    });
  });
');?>

<?=$priceTable?>
