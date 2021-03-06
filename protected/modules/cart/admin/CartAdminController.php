<?php
/**
 * Admin action class for cart module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.cart.admin.CartAdminController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

Yii::import('application.modules.shop.models.*'); // почему-то по другому не работает

class CartAdminController extends \admin\components\HAdminController
{
  /**
	 * @return меню для табов
	 */
  public function tabs()
  {
    return array(
      ''  => 'Все заказы',
      'create'  => 'Оформить заказ',
    );
  }

  /**
	 * Выводит список заказов
	 */
  public function actionIndex($criteria = false, $render = 'render', $id = 'orderIndex')
  {
    // Диалоговые окошки для просмотра чеков
		$this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
      'id'=>'cart',
      'selectors' => array(
        '.ajaxInfo',
      ),
      'themeUrl' => $this->adminAssetsUrl . '/css/jui',
      'options' => array(
        'title'=>'Информация о заказе',
      )
    ));

    $orderModel = new Order('search');
    $orderModel->unsetAttributes();
    if(isset($_GET['Order']))
      $orderModel->attributes=$_GET['Order'];

    if(!$criteria)
    {
      // отображаем фильтр по статусу заказа
      $statusArr = $orderModel->orderStatus;
      $statusIsChecked = array(1=>1, 2, '', '');
      if(isset($_GET['Order']['statusArr']))
        $statusIsChecked = $_GET['Order']['statusArr'];
      $orderModel->statusArr = $statusIsChecked;

      ob_start();
      echo CHtml::beginForm($this->createUrl(''), 'GET');
      echo 'Фильтр по статусу: ';
      foreach($statusArr as $statusId => $statusName)
      {
        echo '<span class="status_' . $statusId . '">' . CHtml::checkBox('Order[statusArr][' . $statusId . ']', $statusIsChecked[$statusId], array(
          'onchange'=>'this.form.submit();',
          'value'=>$statusId,
        )) .
        CHtml::label($statusName, 'Order_statusArr_' . $statusId) . '</span>';
      }
      echo CHtml::endForm();
      ?>
        <p>Цвета контактов: <span class="status_3">Email подтвержден</span>
        <span class="status_1">Email не подтвержден</span>
        <span class="status_4">Гостевой заказ</span>
        </p>
      <?php
      $statusFilter = ob_get_clean();
    }

    $dataProvider = $orderModel->search();

    if ($criteria)
      $dataProvider->criteria->mergeWith($criteria);

		$this->{$render}('table',array(
			'dataProvider'=>$dataProvider,
      'buttons' => array(
        'print',
        'more' => array(
          'visible' => '!empty($data->comment)',
          'options'=>array(
            'class'=>'ajaxInfo',
          ),
        ),
      ),
			'options'=>array(
			  'id' => $id,
			),
      'preTable' => $statusFilter,
			'columns'=>array(
			  'id',
			  array(
			    'name'=>'Товары',
			    'value'=>'
			      call_user_func(function() use ($data)
            {
              // Создаем список превьюшек картинок
              $str = "";
              foreach($data->check as $check)
              {
                $str .= "<div class=\"quantity\">" . $check->prod->img(45) . "<span>" . $check->quantity . "</span></div>";
              }
              return $str;
            })
			    ',
			    'type'=>'raw',
			  ),
			  array(
			    'name'=>'Цена заказа, грн',
			    'value'=>'
			      call_user_func(function() use ($data)
            {
              $str = 0;
              foreach($data->check as $check)
              {
                $str += $check->price*$check->quantity;
              }
              return CHtml::link(number_format($str, 2, ",", " "), "/'.$this->module->id.'/cart/check/".$data->id, array("class"=>"ajaxInfo"));
            })
			    ',
			    'type'=>'raw',
			  ),
			  array(
			    'name'=>'Имя/Фамилия',
			    'value'=>
          '(
          empty($data->user_id) ?
          CHtml::encode($data->client->first_name) . "<br />" . CHtml::encode($data->client->last_name)
          : CHtml::link(CHtml::encode($data->user->first_name) . "<br />" . CHtml::encode($data->user->last_name), $this->createUrl("user", "id" => $data->user_id), array("class"=>"ajaxInfo"))

          )',
			    'type'=>'raw',
			  ),
			  array(
			    'name'=>'type',
			    'value'=>'$data->orderType[$data->type]',
			  ),
			  array(
			    'name'=>'currency',
			    'value'=>'$data->orderCurrency[$data->currency]',
			  ),
			  array(
          'header'=>'Контакты',
          'value'=>'"<span class=\"status_" . ( $data->user->is_active ? "3" : (empty($data->user_id) ? "4" : "1")) . "\">" . (empty($data->user_id) ? CHtml::encode($data->client->email) : CHtml::encode($data->user->email) ) . "</span><br />" . CHtml::encode($data->address->telephone)
           . ($data->address->fullAddress ? "<br />" . $data->address->fullAddress : "") . "<br />" . $data->ip',
           'type'=>'raw',
			  ),
        array(
          'name'=>'operator_id',
          'value'=>'$data->operator->first_name',
        ),
        array(
          'name' => 'date',
          'type' => 'datetime',
        ),
        array(
			    'name'=>'status',
			    'value'=>'
			    CHtml::dropDownList("status_".$data->id, $data->status, $data->orderStatus,
            array(
              "ajax" => array(
                "type"=>"POST",
                "url"=>"'.$this->createUrl('status').'",
                "beforeSend" => "startLoad",
                "complete" => "stopLoad",
                "context" => "js:jQuery(\"#status_" . $data->id . "\")",
                "success"=>"js:function () {jQuery(this).parents(\'td\').prop(\'class\', \'status_\'+jQuery(this).val()); jQuery(this).prop(\'disabled\', jQuery(this).val() > 2)}", //присваеваем стиль статуса
                "data"=>"js:\"status=\"+$(this).val()+\"&id=".$data->id."\"",
              ),
              "disabled" => $data->status > 2,
            ));
			    ',
			    'type'=>'raw',
			    'cssClassExpression' => '"status_". $data->status',
			  ),
      ),
		));
  }

  /**
   *  Доп информация по заказу (выводит комментарий оператора)
   */
  public function actionMore()
  {
    $model = Order::model()->findByPk($this->crudid);
    echo '<h1>' . $model->getAttributeLabel('comment') . '</h1><pre>' . CHtml::encode($model->comment) . '</pre>';

    Yii::app()->end();
  }

  /**
   *  Оформить заказ
   */
  public function actionCreate()
  {
    // так как мы не можем по людски заполнить модель чека
    // мы немнго смахлюем...
    // так как пользователь не заполняет сам скрытые поля (при нормальных условиях)
    // нам нужно проверять только факт наличия данных, остальное проверится на валидации CForm
    if(count($_POST['OrderCheck']))
    {
      $OrderCheck = $_POST['OrderCheck'];
      $_POST['OrderCheck'] = array(
        'order_id'=>1,
        'prod_id'=>1,
        'quantity'=>1,
        'price'=>1,
      );
      // и еще пустышки для address
      $_POST['Order']['address_id'] = $_POST['Order']['user_id'] = 1;
    }

    $form = new CForm('cart.views.admin.addForm');
    $form['order']->model = new Order;
    $form['address']->model = new OrderAddress;
    $form['client']->model = new Client;
    $form['check']->model = new OrderCheck;

    $order = &$form['order']->model;
    $address = &$form['address']->model;
    $client = &$form['client']->model;
    $check = &$form['check']->model;

    if(isset($_POST['ajax']))
    {
      $arr2Validate = array(
        $order,
        $address,
        $client,
        $check,
      );

      echo CActiveForm::validate($arr2Validate);
      Yii::app()->end();
    }

    if($form->submitted('submit') && $form->validate())
    {
      $transaction = Yii::app()->db->beginTransaction();
      try
      {
        $address->user_id = new CDbExpression('NULL');
        if($address->save(false))
        {
          $order->operator_id = Yii::app()->user->id;
          $order->user_id = new CDbExpression('NULL');
          $order->address_id = $address->primaryKey;
          if($order->save(false))
          {
            // добавляем нового клиента
            $client->order_id = $order->primaryKey;
            $client->save(false);
            //$OrderCheck = array('prod_id'=>'quantity');
            $prods = Shop::model()->findAllByAttributes(array('code' => array_keys($OrderCheck)));
            $valid = 1;
            foreach($prods as $prod)
            {
              $check = new OrderCheck;
              $check->order_id = $order->primaryKey;
              $check->prod_id = $prod->primaryKey;
              $check->quantity = $OrderCheck[$prod->primaryKey];
              $check->price = $prod->price * $check->quantity;
              $valid = $valid && $check->save();
            }

            if(!$valid)
              throw new Exception('Не удалось сохранить чек');

            $transaction->commit();

            // все успешно
            Yii::app()->user->setFlash('success', "Заказ оформлен успешно");
            $this->refresh();
          }
        }
      }
      catch (Exception $e)
      {
        // откат транзакции, сообщаем юзеру об ошибке
        $transaction->rollBack();
        Yii::app()->user->setFlash('error', "Ошибка обработки заказа: {$e->getMessage()}");
        $this->refresh();
      }
    }

    // Вторым параметром передаем (captureOutput) true.
    // таким образом мы запустим инициализацию скриптов, но текстовое поле писать не будем,
    // это сделает за нас CForm
    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
      'name' => 'Shop_code',
      'sourceUrl'=>$this->createUrl('acprod'),
      'themeUrl' => $this->adminAssetsUrl . '/css/jui',
      // additional javascript options for the autocomplete plugin
      'options'=>array(
          'minLength'=>'1',
          'focus'=>'js:function( event, ui ) {
            $( "#Shop_code" ).val( ui.item.value );
            return false;
          }',
          'select'=>'js: function( event, ui ) {
            // При выборе нам нужно добавить скрытые поля и строку с инфой о выбранном товаре
            $( "#Shop_code" ).val("");
            $("<div>" + ui.item.label + "</div>")
            .appendTo(
              $("#checkContainer").append("<br>")
            )
            .addClass("checkRow")
            .css({position: "relative", display:"inline-block", padding:"5px"})
            .hover(function(){$(this).css("background", "#333")}, function(){$(this).css("background", "none")})
            .append(
              $("<div>Кол-во: <b>1</b></div>")
              .addClass("quantityVal")
              .css({position:"absolute", bottom: "6px", right: "65px"})
            )
            .append(
              $("<a href>")
              .addClass("icon_sort_desc")
              .css({position:"absolute", bottom: "3px", right: "45px"})
              .click(function() {
                var span = $(this).parent().find(".quantityVal b");
                var val = span.text()*1-1;
                if(val > 0)
                {
                  span.html(val);
                  $(this).parent().find(".quantity").val(val);
                }

                return false;
              })
            )
            .append(
              $("<a href>")
              .addClass("icon_sort_asc")
              .css({position:"absolute", bottom: "3px", right: "25px"})
              .click(function() {
                var span = $(this).parent().find(".quantityVal b");
                var val = span.text()*1+1;
                span.html(val);
                $(this).parent().find(".quantity").val(val);

                return false;
              })
            )
            // кнопка удаления
            .append(
              $("<a href>")
              .addClass("icon_delete")
              .css({position:"absolute", bottom: "5px", right: "5px"})
              .click(function() {$(this).parents(".checkRow").remove();return false;})
            )
            // поля формы
            .append(\'<input type="hidden" name="OrderCheck[\'+ui.item.value*1+\']" value="1" class="quantity" />\')
            ;

            //а также можно задереть противные красные надписи валидатора
            $("#Shop_code").siblings().removeClass("error").removeClass("errorMessage");

            return false;
          }',
      ),
    ), true);


    $js = '
		$("#Shop_code").data( "autocomplete" )._renderItem = function( ul, item ) {
			return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.label + "</a>" )
				.appendTo( ul );
		};';
    Yii::app()->getClientScript()->registerScript(__CLASS__.'#shopProdAutoComplete', $js);

    $check->prod_id = '';

    $this->render('cform_update', array(
      'form' => $form,
    ));
  }

  /**
   *  Product suggestion for autocomplete
   *  @return array JSON array for jQuery UI AutoComplete
   */
  public function actionAcprod()
  {
    $data = new CActiveDataProvider('Shop', array(
      'criteria' => array(
        'condition'=>'code LIKE :code',
        'params' => array(
          ':code' => (int)$_GET['term'] . '%',
        ),
      )
    ));
    $data = $data->data;
    if(count($data))
    {
      foreach($data as $item)
      {
        $itemsArr[] = array(
          'label' => '<div style="overflow:auto"><span style="float:left;margin-right:10px;">' . $item->img(45) . '</span><b>'.$item->product_name.'</b><br /><span style="color:#666666;font-size:10px;">'.$item->code . '</span></div>',
          'value' => $item->code,
        );
      }

      header('Content-type: application/json');
      echo CJSON::encode($itemsArr);
    }
  }

	/**
	 * Меняет статус заказа
	 */
	public function actionStatus()
	{
		if(Yii::app()->request->isPostRequest)
		{
		  $model = Order::model()->findByPk($_POST['id']);
		  $model->status = $_POST['status'];
		  $model->save();
		}
	}

	/**
	 * Выдает более подробную информацию о продуктах в заказе
	 */
	public function actionCheck()
	{
	  $dataProvider=new CActiveDataProvider('OrderCheck', array(
        'criteria'=>array(
            'condition'=>'order_id='.$this->crudId,
        ),
        'sort' => array('defaultOrder' => 'price DESC'),
	    )
	  );

		$this->renderPartial('table',array(
			'dataProvider'=>$dataProvider,
			'buttons'=>array('view'),
			'options'=>array(
			  'id' => 'popup_'.$this->crudId,
			),
			'columns'=>array(
			  array(
			    'name' => 'prod.photo',
			    'value' => '"<div class=\"quantity\">" . CHtml::image(Shop::imgSrc($data->prod->photo[0], 45)) . "<span>" . $data->quantity . "</span></div>"',
			    'type' => 'raw',
			  ),
			 'prod.code',
			 'prod.product_name',
			  array(
			    'name'=>'price',
			    'header'=>'Цена / Сумма грн.',
			    'value' => 'number_format($data->price, 2, ",", " ") . " / " . number_format($data->price*$data->quantity, 2, ",", " ")',
			  ),
        array(
          'name' => 'meta',
          'type' => 'raw',
        ),
			),
		), false, true);
	}

	/**
	* Выдает информацию о всех заказах юзера
	*/
	public function actionUser()
	{
	  $this->actionIndex(array(
      'condition'=>'user_id='.$this->crudId,
	  ), 'renderPartial', 'orderUser');
	}

  /**
   *  Возвращает pdf документ для распечатки бумажки "Подтверждение получения заказа"
   */
  function actionPrint()
  {
    $id = $this->crudId;

    $order = Order::model()->findByPk($id);

    $mpdf = Yii::app()->ePdf->mpdf();
    // ищим файлы с лого (либо тема, либо в вьюхах модуля)
    $viewPathSuffix = '/cart/admin/check/';
    if(!(
      ($theme=Yii::app()->getTheme())!==null
      && is_file($logoPath=$theme->viewPath . $viewPathSuffix . 'logo.png')!==false
      && is_file($logoGrayscalePath=$theme->viewPath . $viewPathSuffix . 'logo_grayscale.png')!==false)
      )
    {
      $logoPath = Yii::getPathOfAlias('cart.views.admin.check') . '/logo_grayscale.png';
      $logoGrayscalePath = Yii::getPathOfAlias('cart.views.admin.check') . '/logo.png';
    }

    $mpdf->logo = file_get_contents($logoGrayscalePath);
    $mpdf->WriteHTML($this->renderPartial('check/check', array('order' => $order), true));

    $mpdf->AddPage();
    $mpdf->logo = file_get_contents($logoPath);
    $mpdf->WriteHTML($this->renderPartial('check/waranty', array('order' => $order), true));

    $mpdf->Output('confirm_' . $order->id . '.pdf', EYiiPdf::OUTPUT_TO_BROWSER);
  }
}
?>
