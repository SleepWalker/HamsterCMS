<?php
/**
 * Controller class for cart module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    cart.controllers.CartController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class CartController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column3';
  
  // переменная в которой хранится вся информация о корзине и заказе
  protected $order;
  protected $stepNum;
  protected $stepParams;

	/**
	 * @return array action filters
	 */
	/*public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
  }*/

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	/*public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index', 'add', 'clear', 'result', 'success', 'order', 'widgetcartstatus'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
  }*/
	
	/**
	*  Показывает корзину юзера
	*  А так же форму для перехода к оформлению заказа
	**/
	public function actionIndex()
	{
	  //если в сессии уже есть order и его элемент quantity - передаем его вьюхе, что бы выводилось актуальное колличество товаров
	  $order = Yii::app()->session['order'];
    
    $cart = $order['cart'];
	  if(count($cart) == 0) $this->redirect('/');

	  if(isset($order['summary']))
	  { // юзер прошел все этапы  по оплате электронными деньгами, показываем ему последний
	    $this->redirect('/cart/order');
	    Yii::app()->end();
	  }
    
    Yii::app()->getClientScript()->registerCoreScript('jquery');
    $this->module->registerScriptFile('cart.js');
	  
	  $this->render('cart', array(
      'models' => $cart,	  
	  ));
	}

  /**
   * Это экшен для аякс запроса обновления статуса корзины  
   * 
   * @access public
   * @return void
   */
  public function actionWidgetCartStatus()
  {
    $this->widget('application.modules.cart.widgets.cartstatus.CartStatus', array(
      'ajax' => true,
    ));
  }
	
	/**
	*  Действие добавление товара в корзину
  *  @param string $id id товара, который нужно добавить в корзину
	**/
	public function actionAdd($id, $_pattern = '<id:\d+>')
  {
    $model = Shop::model()->published()->findByPk($id);
    if(!$model)
      throw new CHttpException(404,'Ошибка добавления в корзину');   
      
    if($model->status != Shop::STATUS_AVAIBLE)
    {
      echo 'Товар <b>' . $model->product_name . '</b> нельзя положить в корзину, так как его нету в наличии.';
      echo '<br />Свяжитесь с нашими операторами для уточнения наличия!';
      Yii::app()->end();
    }
    
    if(empty(Yii::app()->session['order']))
      Yii::app()->session->add('order', array());
    
    $cartContent = Yii::app()->session['order'];  
    $productInfo = (object) array(
      'img' => $model->img(45),
      'product_name' => $model->product_name,
      'price' => $model->price,
      'id' => $model->id,
      'code' => $model->code,
      'viewUrl' => $model->viewUrl,
      'quantity' => 1,
    );
    // характеристики типа варианты
    // запоминаем в сессии выбранный вариант товара (цвет, размер и т.д.)
    if(isset($_POST['variants']))
    {
      $chars = CharShema::model()->findAllByPk(array_keys($_POST['variants']), array(
        'condition' => 'type = ' . CharShema::TYPE_VARIANTS
      ));
      foreach($chars as $char)
        $productInfo->variants[$char->char_name] = CHtml::encode($_POST['variants'][$char->primaryKey]);

      // используем хэш сериализированного POST, для того, что бы 
      // можно было добавить несколько одинаковых товаров разных вариантов
      ksort($_POST['variants']);
      $id .= '_' . md5(serialize($_POST['variants']));
    }

    $cartContent['cart'][$id] = $productInfo;
    Yii::app()->session['order'] = $cartContent;

    // Отключаем jquery (так как при ajax он уже подключен)
    Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 
    $this->renderPartial('_add', array(
      'cart' => $cartContent['cart'],
    )); 
	}
	
	/**
	*  Удаляет елемент из корзины
	*  Если $id=null очищает всю корзину
  *  @param string $id id товара, который нужно удалить из корзины
	*/
	public function actionClear($id = null, $_pattern = '(<id:\d+>)?')
	{
	  if($id)
	  {
	    $cartContent = Yii::app()->session['order'];  
      unset($cartContent['cart'][$id]);
      Yii::app()->session['order'] = $cartContent;
	  }
	  else
	  {
	    Yii::app()->session->remove('order');
	  }
  }

  /**
   * Синоним метода {@link actionClear} для случая, когда надо удалить один товар  
   * 
   * @access public
   * @return void
   */
  public function actionRemove($id, $_pattern = '<id:\w+>')
  {
    $this->actionClear($id);
  }
		
	/**
	*  Выбирает слово в правильном падеже в зависимости от величины числа $n
	*  Пример использования: $this->pluralForm(5, 'товар', 'товара', 'товаров')
  *  @param integer $n число, для которого нужно получить множественную форму
  *  @param string $form1 одиночная форма слова
  *  @param string $form2 форма слова, подходящая для 2 предметов, к примеру два "товара"
  *  @param string $form5 форма слова, подходящая для 5 предметов, к примеру пять "товаров"
	**/
	public function pluralForm($n, $form1, $form2, $form5)
  {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $form5;
    if ($n1 > 1 && $n1 < 5) return $form2;
    if ($n1 == 1) return $form1;
    return $form5;
  }

  /**
  *  Оформление и оплата заказа
  *  Этот метод загружает данные заказа из сессии
  *  Вызывает методы для выбора шага, обработки информации шага, рендеринга шага
  *  Сохраняет новые данные обратно в сессию
  *  После оформления заказа (если это заказ доставки) очищает сессию
  **/
  public function actionOrder()
  {      
    // подгружаем данные из сессии
    $this->order = Yii::app()->session['order'];
    // если пустая корзина - редиректим юзера на главную 
    if(empty($this->order))
      $this->redirect('/');
  
    // проводим проверку наличия данных и на основе этого и данных из $_GET вибираем номер шага
    // этот метод переопределяет значение переменной $this->stepNum
    $this->chooseStep()
    // вызываем метод для обработки данных текущего шага
    ->callStep()
    // рендерим шаг
    ->renderStep($step->params);

    if ($this->stepNum == 4 && empty($this->order['summary']['action']))
    {
      $this->actionClear(); // Чистим сессию, если оплата наличкой   
    }
    else // Сохраняем изменения в сессии 
      Yii::app()->session['order'] = $this->order;
  }
  
  /**
   *  Обертка для вызова шага по его номеру
   *  Кроме вызова нужного шага она еще выполняет общий для каждого шага алгоритм
   *  В качестве номера шага испольщуется $this->stepNum
   *
   *  Принцип, по которому чередуются шаги:
   *    - метод $this->renderStep() выбирает номер шага $this->stepNum (самый ранний шаг, информация которого не сохранена в сессии)
   *    - далее callStep() вызывает метод шага, по номеру $this->stepNum
   *    - если в возвращаемых методом шага параметрах $this->stepParams->valid == true, значит шаг прошел проверку данных и сохранил их
   *    - тогда вызываем метод следующего шага (который запросит у юзера необходимые ему данные)
   *    - если $this->stepParams->valid != true метод прерывается, так как на предыдущем шаге ошибка
   */
  protected function callStep()
  {
    $stepMethodName = 'step'.$this->stepNum;
    if(method_exists($this, $stepMethodName))
      $this->stepParams = $this->$stepMethodName();
    if(!$this->stepParams->valid || $this->stepNum == 4) // Если форма не валидная, рендерим текущий шаг еще раз
      return $this;
      
    // если до сюда дошло, значит предыдущий шаг успешно пройден, потому нам нужно переходить к следующему
    unset($_POST); // Чистим пост перед следующим шагом
    $this->stepNum++;
    return $this->callStep();
  }
  
  /**
   *  Рендерит вьюху для шагов в оплате заказа
   *  В зависимости от запроса использует render или renderPartial
   *  В качестве массива с параметрами вьюхи используется: $this->stepParams
   */
  protected function renderStep()
  {
    $params = $this->stepParams->params;
    // Меняем тип рендеринга в зависимости от запроса
    // Если аякс, нам надо рендерить только часть страницы
    if(Yii::app()->request->isAjaxRequest || $_GET['ajax'])
    {
      $render = 'renderPartial';
      // Отключаем jquery (так как при ajax он уже подключен)
      Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 
    }else{
      $render = 'render';
      $this->module->registerScriptFile('cart.js');
    }
      
    $params['step'] = $this->stepNum;
    // маркер, который включит диалог "Есть ли у вас аккаунт?" на первом шагу по нажатию на кнопку "Далее"
    // это же условие дублируется в chooseStep
    $params['askAboutAccount'] = Yii::app()->user->isGuest && empty($this->order['address']);// && empty($this->order['newUser']);
    // если нам опять нужно спрашивать у юзера о том, как он будет регаться - удаляем переменную-маркер $this->order['newUser']
    if($params['askAboutAccount'] && $this->stepNum == 1) 
      unset($this->order['newUser']);
      
    $this->{$render}('_form', $params, false, true);
  }
  
  /**
   *  Проверяет, можно ли запустить текущий шаг
   *  Меняет значение переменной $this->stepNum на необходимый шаг
   *
   *  last в параметре $_GET['step'] устанавливает шаг с сводной таблицей
   *  Так же этот метод производит обновление колличества товаров в корзине, если информация о них поступила в $_POST
   */
  protected function chooseStep()
  {
    // Сохраняем информацию о товарах из корзины
    if(is_array($_POST['quantity']))
      foreach($_POST['quantity'] as $prodId => $quantity)
        $this->order['cart'][$prodId]->quantity = $quantity;
        
    if(empty($_GET['step'])) $_GET['step'] = 'last';
    $this->stepNum = $_GET['step'] == 'last' ? 3 : $_GET['step'];
    
    /*if(isset($this->order['summary']['action']))
      // скорей всего это повторный возврат на оплату
      // Перескакиваем на 3 этап, если  уже есть элемент summary
      $this->stepNum = 3;*/

    switch($this->stepNum)
    {
      case 4:
        if(!isset($this->order['summary']))
          $this->stepNum = 3;
      case 3:
        if(
         (!isset($this->order['address']) || !isset($this->order['user']))
         || isset($_POST['OrderAddress'])
        )
          $this->stepNum = 2;
      case 2:
        // проверка наличия данных с предыдущего шага
        if(
          !isset($this->order['order']['type'])
          // юзер должен попадать на первый шаг, пока он не заполнит свою контактную информацию
          // это же условие дублируется в renderStep
          || (Yii::app()->user->isGuest && empty($this->order['address']) && empty($_POST['OrderAddress']))
          || isset($_POST['Order']['type'])
        )
          $this->stepNum = 1;
    }
    
    return $this;
  }
  
  /**
   *  Первый шаг в оформлении заказа
   *  На этом шаге у юзера узнают какой способ оплаты, валюту и способ доставки он предпочитает
   */
  protected function step1()
  {   
    $orderModel = new Order;
        
    $orderModel->type = $orderModel->currency = 1; // Значение по умолчанию
    if(isset($this->order['order']['type']) && isset($this->order['order']['currency']))
      // заполняем модель модель ранее полученными данными
      $orderModel->attributes = $this->order['order'];
    if(isset($_POST['Order']['type']))
    {
      $valid = (array_key_exists($_POST['Order']['type'], $orderModel->orderType) && array_key_exists($_POST['Order']['currency'], $orderModel->orderCurrency));
      if($valid)
      {       
        // сохраняем тип оплаты и доставки
        $orderModel->attributes = $this->order['order'] = $_POST['Order'];
        
        // пользователь решился зарегистрироваться
        if(isset($_POST['newUser']))
          $this->order['newUser'] = $_POST['newUser'];
      }else
        $orderModel->addError('type', 'Выберите правильные способ оплаты и доставки');
    }

    return (object)array(
      'valid' => $valid,
      'params' => array(
        'model' => $orderModel,
      ),
    );
  }
  
  /**
   *  Второй шаг в оформлении заказа
   *  На этом шаге юзер вводит свои контактные данные
   */
  protected function step2()
  {    
    // если тип заказа соответствует заказу с доставкой, активируем соответствующий сценарий
    $adressModel = new OrderAddress( ($this->order['order']['type'] == 2?'':'delivery') );

    if(Yii::app()->user->isGuest)
    {
      if($this->order['newUser']) // создаем модель для регистрации
        $userModel = new User('register');
      else // создаем модель для хранения гостевых заказов
        $userModel = new Client('withEmail');
    }
    else // Для залогиненого подгружаем его данные
    {
      $userModel = User::model()->findByEmail();
      $this->order['userRegistered']['id'] = $userModel->id;
      $this->order['userRegistered']['first_name'] = $userModel->first_name;
      $this->order['userRegistered']['last_name'] = $userModel->last_name;
      $this->order['userRegistered']['email'] = $userModel->email;
    }

    // заполняем форму данными, если такие есть
    $userModel->attributes = $this->order['user'];
    $adressModel->attributes = $this->order['address'];
    
    if(isset($_POST['newAddress']) || isset($_POST['oldAddress']) || isset($_POST['OrderAddress']))
    {
      $userModel->attributes = $_POST[get_class($userModel)];
      $adressModel->attributes = $_POST['OrderAddress'];
      
      if($_POST['oldAddress'] && !$_POST['newAddress'])
      {
        // Юзер выбрал один из ранее веденных адресов
        $this->order['oldAddress'] = $_POST['oldAddress'];
        $valid = 1;
      }
      else
      {
        $this->performAjaxValidation(array($userModel, $adressModel));
        $this->order['oldAddress'] = false;
        $valid = $adressModel->validate();
      }
      
      $valid = $userModel->validate() && $valid;
      
      if($valid)
      {
        // Сохраняем массивы пост в сессию, что бы потом обработать
        $this->order['address'] = $_POST['OrderAddress'];
        $this->order['user'] = $_POST[get_class($userModel)];
      }
    } 

    return (object)array(
      'valid' => $valid,
      'params' => array(
        'model1' => $userModel,
        'model2' => $adressModel,
        'oldAddress' => $this->order['oldAddress'],
      ),
    );
  }
  
  /**
   *  Третий шаг в оформлении заказа
   *  На этом шаге мы выводим сводную таблицу с информацией заказа
   *  На этом шаге не производится никакой обработки данных, кроме парсинга уже имеющихся
   */
  protected function step3()
  {
    // дабы хоть раз показать этот шаг ставим ему $valid = 0
    $valid = 0;
    // вводим коефициент для конвертации валюты для разных способов оплаты
    if($this->order['order']['currency'] > 1 && Yii::app()->params->currency['toEmoney'] > 0 && Yii::app()->params->currency['toDollar'] > 0)
      $currencyCoeff = Yii::app()->params->currency['toEmoney']/Yii::app()->params->currency['toDollar'];
    else
      $currencyCoeff = 1;
    $paymentAmount = 0; // Сумма заказа
    foreach($this->order['cart'] as $cartProduct)
    {
      // суммарное колличество товаров
      $prodCount += $cartProduct->quantity;
      // сумма заказа
      $paymentAmount += $cartProduct->price  * $cartProduct->quantity * $currencyCoeff;

      // варианты товара (на пример разный цвет, размер и т.д.)
      if(isset($cartProduct->variants))
      {
        $variants =  '<dl>';
        foreach($cartProduct->variants as $name => $value)
          $variants .= "<dt>$name</dt><dd>$value</dd>";
        $variants .= '</dl>';
      }else{
        $variants = '';
      }
      // Массив для CGridView, что бы выводить информацию о заказе
      $orderInfo[] = array(
        '<a href="' . $cartProduct->viewUrl . '" target="_blank">' . $cartProduct->img . '</a>',
        '<a href="' . $cartProduct->viewUrl . '" target="_blank"><b>' . $cartProduct->product_name . '</b></a>' . $variants,
        $cartProduct->quantity,
        number_format($cartProduct->price * $currencyCoeff, 2, ',', ' ') . ' грн.' .
        ($cartProduct->quantity > 1 ? '<br /> <b>' . number_format($cartProduct->price  * $cartProduct->quantity * $currencyCoeff, 2, ',', ' ') . ' грн.</b>' : ''),
      );
    }
    
    $orderModel = new Order;
    
    $this->order['summary'] = array(
      'orderPrice' => number_format($paymentAmount, 2, ',', ' '),
      'amount' => $paymentAmount, // для класса Emoney
      'prodCount' => $prodCount,
      'type' => $orderModel->orderType[$this->order['order']['type']],
      'delivery' => $this->order['order']['type'] == 1,
      'currency' => $orderModel->orderCurrency[$this->order['order']['currency']],
      'orderInfo' => $orderInfo,
    );

    // Если юзер выбрал старый адрес, переносим его параметры в массив address
    if($this->order['oldAddress'])
    {
      $model = OrderAddress::model()->findByPk($this->order['oldAddress']);
      $this->order['address']['street'] = $model->street;
      $this->order['address']['house'] = $model->house;
      $this->order['address']['flat'] = $model->flat;
      $this->order['address']['telephone'] = $model->telephone;
    }
    
    if(isset($this->order['userRegistered']))
      $this->order['user']['first_name'] = $this->order['userRegistered']['first_name'];
    
    return (object)array(
      'valid' => $valid,
      'params' => array(
        'summary' => $this->order['summary'],
        'order' => $this->order['order'],
        'user' => $this->order['user'],
        'address' => $this->order['address'],
      ),
    );
  } 
  
  /**
   *  Четвертый шаг в оформлении заказа
   *  На этом шаге производится обработка данных заказа и сохранение их в бд
   */
  protected function step4()
  {
    // Начинаем транзакцию
    $transaction = Yii::app()->db->beginTransaction();
    try
    {
      $valid = 1;
      // для случая оплаты электронными деньгами
      // если элемент массива $this->order['summary']['action'] уже определен, значит юзер уже пытался оплачивать заказ
      // потому пропускаем обработку данных заказа
      // при оплате наличкой к этот блок кода будет выполняться один раз
      if(!isset($this->order['summary']['action']))
      { // Производим обработку введенных данных
        $orderModel = new Order;
        $userId = $this->order['userRegistered']['id'];
        // если это новый юзер/гостевой заказ
        if(empty($userId))
        {
          // регистрируем юзера, в том случае, если он ввел пароли
          if(!empty($this->order['user']['password1']))
          {
            $userModel = new User('register');
            // подгружаем данные из сессии
            $userModel->attributes = $this->order['user'];
            $valid = $userModel->save();
            // авторизируем юзера
            $userModel->login();
            // Отправляем письмо с подтверждением email адреса
            $userModel->sendMailConfirm();
            if(!$valid)
              Yii::log("UserModel: \n" . CVarDumper::dumpAsString($this->order['user']) . "\n\nErrors: \n" . CVarDumper::dumpAsString($userModel->errors), 'error', 'order');
            $userId = $userModel->id;
          }
          else
          {
            // Пишем контактные данные юзера в таблицу order_client
            $clientModel = new Client('withEmail');
            // подгружаем данные из сессии
            $clientModel->attributes = $this->order['user'];
            
            // задаем null как id юзера, что бы переключить на order_client
            $userId = new CDbExpression('NULL');
          }
        }     
        
        $addressId = $this->order['oldAddress'];
        // если юзер ввел новый адрес
        if(empty($addressId))
        {
          // если тип заказа соответствует заказу с доставкой, активируем соответствующий сценарий
          $adressModel = new OrderAddress( (($this->order['order']['type'] == 2)?'':'delivery') );
          // подгружаем данные из сессии
          $adressModel->attributes = $this->order['address'];
          // вставляем id юзера
          $adressModel->user_id = isset($this->order['userRegistered']) ? $this->order['userRegistered']['id'] : $userId;
          $valid = $adressModel->save() && $valid;
          if(!$valid)
            Yii::log("AddressModel: \n" . CVarDumper::dumpAsString($this->order['address']) . "\n\nErrors: \n" . CVarDumper::dumpAsString($adressModel->errors), 'error', 'order');
          $addressId = $adressModel->primaryKey;
        }
          
        $this->order['order']['user_id'] = $userId;
        $this->order['order']['address_id'] = $addressId;
        
        // Если оплата электронными деньгами
        // Присваеваем статус "отмененного" заказа, до того момента, пока не произойдет оплата
        if($this->order['order']['currency'] > 1  && $this->order['order']['currency'] != 8)
          $this->order['order']['status'] = 4; 
          
        // подгружаем данные из сессии
        $orderModel->attributes = $this->order['order'];

        $valid = $orderModel->save() && $valid;
        if(!$valid)
            Yii::log("OrderModel: \n" . CVarDumper::dumpAsString($this->order['order']) . "\n\nErrors: \n" . CVarDumper::dumpAsString($orderModel->errors), 'error', 'order');
         
        // если пользователь не захотел регистрироваться во время заказа
        if(isset($clientModel))
        {
          $clientModel->order_id = $orderModel->primaryKey;
          $valid = $clientModel->save() && $valid;
          if(!$valid)
            Yii::log("clientModel: \n" . CVarDumper::dumpAsString($this->order['user']) . "\n\nErrors: \n" . CVarDumper::dumpAsString($clientModel->errors), 'error', 'order');
        }
        
        // Создаем чек
        $paymentAmount = 0; // Сумма заказа
        // вводим коефициент для конвертации валюты для разных способов оплаты
        if($this->order['order']['currency'] > 1 && Yii::app()->params->currency['toDollar']>0 && Yii::app()->params->currency['toEmoney']>0)
          $currencyCoeff = Yii::app()->params->currency['toEmoney']/Yii::app()->params->currency['toDollar'];
        else
          $currencyCoeff = 1;
        foreach($this->order['cart'] as $cartProduct)
        {
          $checkModel = new OrderCheck;
          $checkModel->order_id = $orderModel->primaryKey;
          $checkModel->prod_id = $cartProduct->id;
          $checkModel->quantity = $cartProduct->quantity;
          $checkModel->price = $cartProduct->price * $currencyCoeff;
          if(isset($cartProduct->variants))
            $checkModel->meta = serialize($cartProduct->variants);
          $valid = $checkModel->save() && $valid;
          
          $paymentAmount += $checkModel->price * $cartProduct->quantity;
          if(!$valid)
            Yii::log("CheckModel: \n" . CVarDumper::dumpAsString($checkModel->errors), 'error', 'order');
        }
        
        // Если оплата электронными деньгами, создаем массив с параметрами
        if($orderModel->currency > 1 && $orderModel->currency != 8)
        {
          if($orderModel->currency < 5 ) // WM
            $merchantName = 'WM';
          elseif($orderModel->currency < 8 ) // Privat24
            $merchantName = 'Privat24';
          
          // генерируем форму для оплаты электронными деньгами
          // сохраняем ее в кэш, для последующего использования при валидации запросов от мерчанта
          $emoney = Emoney::choose($merchantName)
          ->createPayment(array(
            'orderNo' => $orderModel->id,
            'amount' => $paymentAmount,
            'desc' => Yii::app()->params['shortName'] . ': Оплата заказа №'.$orderModel->primaryKey,
          ))
          ->save();
          
          $this->order['summary']['action'] = $emoney->formAction;
          $this->order['summary']['fields'] = $emoney->formFields;
        }
        // добавляем номер заказа и дату
        $this->order['summary']['orderNo'] = $orderModel->id;
        $this->order['summary']['orderDate'] = Yii::app()->dateFormatter->formatDateTime((string)$orderModel->date);
      }// if(!isset($this->order['summary']['action']))
      
      //коммитим транзакцию
      $transaction->commit();
    }
    catch (Exception $e)
    {
      // откат транзакции, сообщаем юзеру об ошибке
      $transaction->rollBack();
      $valid = 0;
    }

    if($valid && empty($this->order['summary']['action']))
    {
      // отправляем письмо о успешном оформлении заказа
      $this->sendOrderSummary();
    }
      
    return (object)array(
      'valid' => $valid,
      'params' => array(
        'valid' => $valid,
        'emoneyAction' => $this->order['summary']['action'],
        'emoneyFields' => $this->order['summary']['fields'],
      ),
    );
  }
  
  /**
  *   Обработка запросов от мерчантов
  **/
  public function actionResult()
  {
    Emoney::chooseFromPost()->onSuccess(array($this, 'emoneySuccessHandler'))->resultAction();
  }
  
  /**
  *   Обработчик события успешной оплаты
  **/
  public function emoneySuccessHandler($event)
  {
    $orderModel = Order::model()->findByPk($event->sender->orderNo);
    $orderModel->status = Order::NOT_COMPLETE;
    $orderModel->save();
  }
  
  /**
  * Очищает сессию и перенаправляет юзера на page/success
  **/
  public function actionSuccess()
  {
    Emoney::chooseFromPOST()->successAction();
    // отправляем письмо о успешном оформлении заказа
    $this->sendOrderSummary();
    
    $this->actionClear();
    $this->redirect(array('/page/index', 'path'=>'success'));
  }
  
  /**
	*  Отправляет письмо пользователю с оповещением об успешной оплате заказа
	**/
	public function sendOrderSummary()
	{
    
	  $message = new YiiMailMessage;
    $message->view = 'orderSummary';
    
    $user = isset($this->order['userRegistered']) ? $this->order['userRegistered'] : $this->order['user'];
    
    $message->setBody(array(
      'user' => $user,
      'summary' => $this->order['summary'],
      'address' => $this->order['address'],
      'cart' => $this->order['cart'],
    ), 'text/html');
      
    
    $message->addTo($user['email']);

    // отправляем скрытые копии для операторов
    $bccEmails = Yii::app()->modules['cart']['params']['bccEmails'];
    if(!empty($bccEmails))
    {
      if(strpos($bccEmails, ',')) $bccEmails = preg_split('/ *, */', $bccEmails);
      else $bccEmails = array($bccEmails);
      $message->setBcc($bccEmails);
    }

    $message->subject = 'Информация о заказе №' . $this->order['summary']['orderNo'];
    $message->from = array(Yii::app()->params['noReplyEmail'] => Yii::app()->params['shortName']);
    Yii::app()->mail->send($message);
	}
  
  /**
  * AJAX валидация формы
  **/
  protected function performAjaxValidation(array $models)
  {
      if(isset($_POST['ajaxValidate']))
      {
        echo CActiveForm::validate($models); 
        Yii::app()->end();
      }
  }
}
