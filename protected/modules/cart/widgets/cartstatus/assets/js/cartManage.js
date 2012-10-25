$(function() {
  /**
   *  Глобально проставляет колличество товаров/сумму в корзине
   */
  function cartSetValues(quantity, summ)
  {
    // Самый топорный метод (в некоторых случаях - единственный)
    renderCartStatus(quantity, summ);
    // Устанавливает сумму с учетом множественного числа у слова "товар"
    if (quantity !== false)
    {
      $('.qtotal').each(function()
      {
        $(this).parent().html(
          $(this).parent().html().replace(/товара?о?в?/g, pluralForm(quantity, 'товар', 'товара', 'товаров'))
        );
      });
      $('.qtotal').html(quantity);
    }
  }
  // делаем функцию глобальной
  window.cartSetValues = cartSetValues;
  
  /**
  * Форматирует число как цену
  **/
  function number_format(number)
  {
    number = parseInt(number).toFixed(2);
    number = number.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ').replace(".", ",");
    return number;
  }
  
  /**
	*  Выбирает слово в правильном падеже в зависимости от величины числа $n
	*  Пример использования: $this->pluralForm(5, 'товар', 'товара', 'товаров')
	**/
	function pluralForm($n, $form1, $form2, $form5)
  {
    $n = Math.abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $form5;
    if ($n1 > 1 && $n1 < 5) return $form2;
    if ($n1 == 1) return $form1;
    return $form5;
  }
  
  function renderCartStatus(quantity, summ)
  {
    if(quantity) // корзина не пустая
      content = $('<a>').prop('href', '/cart/').html('В вашей корзине <b class="qtotal">' + quantity + '</b> ' + pluralForm(quantity, 'товар', 'товара', 'товаров') + ' на сумму <b class="sumtotal">' + number_format(summ) + ' грн.</b>');
    else
      content = 'Ваша корзина';// <b>пустая</b>
    $('#cartStatusWidget').html(content);
  }
  
  // делаем функцию глобальной
  window.renderCartStatus = cartSetValues;
});