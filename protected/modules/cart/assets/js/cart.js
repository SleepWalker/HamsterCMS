$(function() {
  /*
  * Изменение количества товаров
  */
  $('body').on('change click', '.quantity', function() {
    var id = $(this).prop('id').slice(1);
    var newQuantity = $(this).val()*1;
    quantity[id] = newQuantity;
    
    // Обрабатываем цену (сумма, что напротив товара)
    var priceTd = $(this).parents('td').prev();
    if(!priceTd.find('.prodSumm').length)
      priceTd.append('<div class="prodSumm">' + priceTd.html() + '</div>');
    priceTd = priceTd.find('.prodSumm');
    var newPrice = number_format(price[id] * quantity[id]);
    
    priceTd.html(newPrice + priceTd.html().replace(/[\d,]/g, ''));
    
    // Обновляем сумму и колличество товаров
    countSummary();
  });

  /*
  * Удаление из корзины
  */
  $('body').on('click', '.delLink', function() {
    var row = $(this).parents('tr');
    var id = $(this).prop('id').slice(1);
    $.ajax({
      url: $(this).prop('href'),
      success: function()
      {
        delete price[id];
        delete quantity[id];
        // Обновляем сумму
        countSummary();
        
        // сообщаем, что корзина пуста и переадресовываем юзера на главную
        if(row.parents('table').find('tr').length <= 2  && location.href.indexOf('/cart') != -1) // 2 потому, что одну строку мы сейчас удалим и еще одна строка с суммой
        { 
          $('#yt0').hide(); // прячем кнопку продолжения заказа
          runDialog('<br />Ваша корзина пуста, через <span id="redirectTimer">3</span> секунд вы будете перемещены на <a href="/">главную страницу</a>');
          setInterval(function() {
            var wait = $("#redirectTimer").html()*1;
            if(wait)
              $("#redirectTimer").html(--wait);
            else 
              location.href = "/";
          }, 1000);
        }     
        row.remove();
      } 
    }); 
    return false;
  });

  /**
  * Обновляет сумму покупки
  **/
  function countSummary()
  {
    var summ = 0;
    var amount = 0;
    for (i in price)
    {
      summ += price[i] * quantity[i];
      amount += quantity[i] * 1;
    }
      
    // устанавливаем сумму и колличество товаров (функция, предоставляемая виджетом cartStatus)
    cartSetValues(amount, summ);
    
    summ = number_format(summ);
    $('#summary').html(summ + $('#summary').html().replace(/[\d,]/g, ''));
  }

  /**
  * Форматирует число как цену
  **/
  function number_format(number)
  {
    number = number.toFixed(2);
    number = number.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ').replace(".", ",");
    return number;
  }
  var d = window.document;
  var timer = false;
  /***********************
  * Отображает анимацию загрузки
  ***********************/
  window.startLoad = function() 
  {
    if (timer) return;
    if (d.getElementById('loading_layer')) return;
    // Блокируем все submit
    $('[type="submit"]').prop('disabled', 'disabled');
    
    var loading_layer = d.createElement('div');
    loading_layer.id = 'loading_layer';
    loading_layer.className = 'ajax_loading';
    
    // Что бы не портить впичатление юзера от быстрого интерфейса 
    // включаем задержку до появления загрузчика
    timer = setTimeout(function(){
      d.body.appendChild(loading_layer);
    }, 1000);
  }

  /***********************
  * Прекращает анимацию загрузки
  ***********************/
  window.stopLoad = function() {
    if(timer)
      clearTimeout(timer);
    if ($('#loading_layer').parents('body')) // Значит элемент уже в дом
      $('#loading_layer').remove();
  }

  /**
   *  Обновляет контент блока с анимацией (используется в корзине)
   */
  window.replaceContent = function (data)
  {
    $('[type="submit"]').removeProp('disabled');
    $('#cartContent').fadeOut(500, function()
    {
      $('#cartContent').replaceWith(data);
      $('html, body').animate({scrollTop:$('#cartContent').offset().top}, 'slow'); 
      $('#cartContent').fadeIn(500); 
    });
  }
});
