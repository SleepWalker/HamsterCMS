<?php
/**
 * Site controller class.
 * Provides authentication (login, logout, register, change password, accaount activation),
 * contact and error displaying functionality
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class SiteController extends Controller
{
  public $layout='//layouts/column3';
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
        'foreColor'=>0x980d0d,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		//$this->render('index');
		throw new CHttpException(404,'Запрашиваемая страница не существует.');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if($_POST['ajax'] || $_POST['ajaxSubmit'] || $_POST['ajaxaction'] || $_POST['ajaxIframe'])
	    		echo CJSON::encode(array(
	      		'action'=>404, 
	      		'content'=>$error['message']
	    		));
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
			/**
			 * Меняем логику по принципам MVC
			 * Письмо создаем и отсылаем в контроллере
			 * Все данные необходимые для него запрашиваем из модели
			 * Текст рендерим во вьюхе (с)Мастир
			 */
				$message = new YiiMailMessage();
				$message->addTo(Yii::app()->params['adminEmail']);
				$message->from = array(Yii::app()->params['noReplyEmail'] => Yii::app()->params['shortName']);
				$message->view = $model->getView();
				$message->setBody(array('data'=>$model), 'text/html');
				$message->subject = $model->getSubject();
				foreach($model->getFiles() as $file)
					$message->attach($file);
				if(Yii::app()->mail->send($message))
					Yii::app()->user->setFlash('contact',$model->getSendSuccessMessage());
				else
					$model->getSendFailMessage();
				$this->refresh();
			} else {
				$model->getSendFailMessage();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{	   
	  if(!Yii::app()->user->isGuest)
  	  $this->redirect();
  	  
  	$renderType = ($_GET['ajax'])?'renderPartial':'render';
  	if ($_GET['ajax'])
  	 Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;  
	   
		$model=new LoginForm;
    
    // ставим по умолчанию галочку rememberMe
    $model->rememberMe = 1;
    
		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
        if(!$_GET['ajax'])
          $this->redirect();
			 else
			 {
			   echo 'ok';
			   Yii::app()->end();
			 }
    }else
      // страница, на которую вернется пользователь.
      Yii::app()->user->returnUrl = Yii::app()->getRequest()->urlReferrer;
    
		// display the login form
		$this->{$renderType}('login',array('model'=>$model), false, !empty($_GET['ajax']));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect();
	}
	
	/**
	*  Регистрация пользователя
	**/
	public function actionRegister()
  {
    Yii::import('application.modules.user.models.*');

	  $this->pageTitle = 'Регистрация';
	  
	  if(!Yii::app()->user->isGuest)
  	  $this->redirect('/');
  	  
    $model = new User('register');
    
    // AJAX валидация
		if(isset($_POST['ajax']))
		{
      $model->attributes = $_POST['User'];
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

    if(isset($_POST['User']))
	  {
	    $model->attributes = $_POST['User'];
	    
	    if ($model->save())
	    {
	     $model->sendMailConfirm(); // Отправляем письмо с ссылкой подтверждения Email

       if(isset($_POST['User']['role']))
         // включена возможность выбирать роли при регистрации
         // перенаправим обработку этого выбора на модель AuthItem
         $authItem = AuthItem::model()->addToTransfer($model, $_POST['User']['role']);

	     $this->renderText('
  	     <h1>Успешная регистрация</h1>
  	     <p>Ваш аккаунт был успешно зарегистрирован. Вскоре на ваш почтовый ящик придет письмо с ссылкой для активации аккаунта.</p>
  	     <p>Вернуться на <a href="/">главную страницу</a> или воспользоваться формой входа:</p>
  	     <p><a href="/site/login">Войти на сайт</a></p>
	     ');
       Yii::app()->end();
	    }
	  }
	  
	  $this->render('register', array(
      'model' => $model,	
	  ));
	}
	
	/**
	*  Подтверждение адресса Email
	**/
	public function actionConfirm()
	{
	  $model = User::model()->findByEmail($_GET['email']);
	  if($model && !$model->is_active && $_GET['h'] == $model->confirmHash)
	  {
	    $model->is_active = 1;
	    if($model->save())
	     $this->renderText('
  	     <h1>Активация аккаунта прошла успешно</h1>
  	     <p>Теперь вы сможете получать специальные скидки и участвовать в акциях нашего магазина.</p>
  	     <p>Для продолжения работы с сайтом вернитесь на <a href="/">главную страницу</a>.</p>
  	     <p>Если вы еще не авторизовались, можете воспользоваться формой входа:</p>
  	     <p><a href="/site/login">Войти на сайт</a></p>
	     ');
	  }elseif($model && !$model->is_active){
	    $model->sendMailConfirm(); // Отправляем письмо с ссылкой подтверждения Email
  	  $this->renderText('
  	    <h1>Активация аккаунта не удалась</h1>
  	    <p>На ваш почтовый ящик было выслано повторное письмо для активации</p>
      ');
	  }else{
	   throw new CHttpException(404,'Ошибка. Такого аккаунта не существует, либо он уже активирован');
	  }
	}
	
	/**
	 * Форма смены пароля
	 */
	public function actionChpass()
	{	
	  if($_POST['h'])
	  { // Сохраняем новый пароль
	    $model = User::model()->findByEmail($_GET['email']);
  	  if($model && $_POST['h'] == $model->chpassHash && $_POST['User'])
  	  {
  	    $model->scenario = 'register';
  	    $model->attributes = $_POST['User'];
  	    
  	    if($model->save())
        {
  	      $this->renderText('
    	     <h1>Смена пароля прошла успешно</h1>
    	     <p>Для продолжения работы с сайтом вернитесь на <a href="/">главную страницу</a>.</p>
    	     <p>Или можете воспользоваться формой входа:</p>
    	     <p><a href="/site/login">Войти на сайт</a></p>
           ');
          Yii::app()->end();
        }
  	  }
	  }

    $model = new User;
	  
	  if($_GET['h'])
	  { // Проверяем хеши и выводим форму для смены пароля
	    if(!Yii::app()->request->isPostRequest) // если пост - модель уже создана
	     $model = User::model()->findByEmail($_GET['email']);
  	  if($model && $model->is_active && $_GET['h'] == $model->chpassHash)
  	  {
        $model->scenario = 'register';
        // далее выполнится рендер в конце файла
  	  }else{
  	   throw new CHttpException(404,'Ошибка восстановления пароля');
  	  }
	  }
	  
	  if($_POST['User'])
	  {
	    $model = User::model()->findByEmail($_POST['User']['email']);
	    if($model)
	    {
	      $model->sendChpassMail();
  	    $this->renderText('
    	     <h1>Восстановление пароля</h1>
    	     <p>На указанный вами Email было отправленно письмо с ссылкой для восстановления пароля.</p>
    	     <p>Для продолжения работы с сайтом вы можете вернуться на <a href="/">главную страницу</a>.</p>
	     ');
        Yii::app()->end();
	    }
	    else
      {
	      $this->renderText('
    	     <h1>Ошибка</h1>
    	     <p>Такого Email не существует. Вы можете попробовать <a href="' . Yii::app()->createUrl('site/chpass') . '">еще раз</a></p>
    	     <p>Для продолжения работы с сайтом вы можете вернуться на <a href="/">главную страницу</a>.</p>
           ');
        Yii::app()->end();
      }
	  }
    
    $this->render('chpass', array(
      'model'=>$model,
    ));
	}
	
	/**
	*  Редирект
	**/
	public function redirect($referrer = false)
  {
    if (!$referrer)
    {
      $referrer =  '/' . str_replace(Yii::app()->createAbsoluteUrl('/'), '', Yii::app()->getRequest()->urlReferrer);
      $requestUri = Yii::app()->request->requestUri;
      if($referrer == $requestUri && Yii::app()->getRequest()->urlReferrer)
        $referrer = Yii::app()->user->returnUrl;
      elseif(Yii::app()->getRequest()->urlReferrer)
        $referrer = Yii::app()->request->urlReferrer;
      else
        $referrer = '/';
    }

    parent::redirect($referrer);  
	}
}
