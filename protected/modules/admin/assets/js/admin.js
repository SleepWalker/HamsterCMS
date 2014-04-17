var d = document;

/***********************
* Создание пользовательских событий
***********************/
var ev = {
  evArr: new Array(),
  cast: function (eName) {
    for (var fName in this.evArr[eName]) {
      this.evArr[eName][fName](arguments);
    }
  },
  attach: function (eName, func, fName) {
    if (typeof this.evArr[eName]  != 'object') {
      this.evArr[eName] = new Array();
    }
    this.evArr[eName][fName] = func;
  },
  detach: function (eName, fName) {
    delete this.evArr[eName][fName];
  }
};

/*******************
 *
 * Выпадающее меню шапки
 *
 ******************/
var menuLinks = d.getElementById('menu').getElementsByTagName('a');
var menuTimer, activeMenu = false;

for (var i = 0; i < menuLinks.length; i++) {
  if (document.getElementById('menu' + i)) {
    menuLinks[i].onmouseover = function(i) {return function() {showSubMenu(i)}}(i);
    menuLinks[i].onmouseout = function(i) {return function() {hideSubMenu(i)}}(i);
  }
}

function showSubMenu(sMenuNum) {
  clearTimeout(menuTimer);
  if (activeMenu && activeMenu != sMenuNum) {
    d.getElementById('menu' + activeMenu).style.display = 'none';
  }
  activeMenu = sMenuNum;
  var subMenu = d.getElementById('menu' + sMenuNum);
  subMenu.style.display = 'block';
  var leftCoord = getOffsetRect(menuLinks[sMenuNum]);
  //вычисляем координату средины пункта меню и отнимаем от нее половину ширины выпадающего меню
  var delta = Math.round(leftCoord.left + menuLinks[sMenuNum].offsetWidth/2 - subMenu.offsetWidth/2);
  subMenu.style.left = delta + 'px';
  if (!subMenu.onmouseover) {
    subMenu.onmouseover = function() {showSubMenu(sMenuNum)};
    subMenu.onmouseout = function() {hideSubMenu(sMenuNum)};
  }
}

function hideSubMenu(sMenuNum) {
  var subMenu = d.getElementById('menu' + sMenuNum);
  menuTimer = setTimeout(function(){subMenu.style.display = 'none';activeMenu = false;}, 300);
}

/*******************
 *
 * Сворачивание шапки
 *
 ******************/
hookEvent(window, 'scroll', headerCollapse);

function headerCollapse() {
  var header = d.getElementById('header');
  if (d.documentElement.scrollTop > 60 || d.body.scrollTop > 60) {
    header.className = 'header_small';
    }else{
    header.className = 'header_big';
  }
}

/*******************
 *
 * Выравнивание breadcrumps
 *
 ******************/
var breadcrumps = getElementsByClass('breadcrumps', d.body, 'ul');
for (var i = 0; i < breadcrumps.length; i++) {
  var breadcrumpsLi = breadcrumps[i].getElementsByTagName('li');
  q = breadcrumpsLi.length;
  for (var j = 0; j < breadcrumpsLi.length; j++) {
    breadcrumpsLi[j].style.zIndex = q--;
  }
}

/*******************
 *
 * Вкладки
 *
 ******************/
var tabContainer = getElementsByClass('tabs', d.body, 'div');
for (var i = 0; i < tabContainer.length; i++) {
  var a = tabContainer[i].getElementsByTagName('a');
  openTab(a[0]);
  for (var j = 0; j < a.length; j++) {
    //Проверяем адресную строку, что бы подкрасить вкладку, соответствующую странице
     if (location.href.indexOf(a[j].href) > -1) {
      // текущий способо задавания вкладок в Yii удобен, но у него есть недостаток, нельзя указать id в ссылке
      // потому ищем ссылку, которая отличается от url в строке браузера лишь цифровой частью и присваеваем его параметру href
        if( /^[0-9]+$/.test( location.href.replace(a[j].href, '').replace('/','') ) ) 
          a[j].href = location.href;
        openTab(a[j]);
     }
     //В случае если это действительно вкладка а не ссылка
    if(a[j].hash)
      a[j].onclick = function () {openTab(this);return false;};
  }
}

function resetTabs(tabContainer) {
  var a = tabContainer.getElementsByTagName('a');
  for (var i = 0; i < a.length; i ++) {
    a[i].className = '';
    if (a[i].hash != '')
      if (d.getElementById(a[i].hash.slice(1)))
        d.getElementById(a[i].hash.slice(1)).style.display = 'none';
  }
}

function openTab(a) 
{
  if(!a) return;
  resetTabs(a.parentNode);
  if (a.hash != '')
    if (d.getElementById(a.hash.slice(1)))
      d.getElementById(a.hash.slice(1)).style.display = 'block';
  a.className = 'active';
}

/*******************
 * Коды
 ******************/
var icodes = getElementsByClass('icodes', d.body, 'input');
for (var i = 0; i < icodes.length; i++) {
  icodes[i].onclick = function() {this.select();}
}

/*******************
 * Выпадающий список
 ******************/
var dd_menu = getElementsByClass('drop_down', d.body, 'div');
for (var i = 0; i < dd_menu.length; i++) {
  dd_menu[i].getElementsByTagName('div')[0].onclick = function(i){ return function () {manageDd(dd_menu[i])}}(i);
}
function manageDd(obj) {
  if(obj.className == 'drop_down') {
    obj.className = 'drop_down_active';
  }else{
    obj.className = 'drop_down';
  }
}

/**
 * Табличный ввод для update вьюхи
 */
$('.container-tabular').on('click', 'a', function() {
  if($(this).hasClass('icon_delete'))
  {
    $(this).parent().hide(300, function() {$(this).remove()});
    return false;
  }
  var nextIndex = $(this).data('model-count'),
      $last = $(this).parent().find('.row-tabular').last(),
      $newForm = $last.clone(),
      replace = function(el, prop, start, end)
      {
        var $el = $(el);
        if(!$el.is('['+prop+']'))
          return;

        if(!end) end = start;
        var regExp = new RegExp('([\\[_])\\d+([\\]_])', 'g');
        var replacement = $el.prop(prop).replace(regExp, '$1'+nextIndex+'$2');
        $el.prop(prop, replacement);
      }

  $newForm.find('input, textarea, select, label').each(function() {
    replace(this, 'name');
    replace(this, 'id');
    replace(this, 'for');
    $(this).val('');
  });

  $newForm.find('.dnd_drager input').val(nextIndex);

  $newForm.insertAfter($last);
  $(this).data('model-count', ++nextIndex);

  return false;
});

/***********************
* Drag&Drop блоки, инициализация
***********************/
var dnd_wrapper = d.getElementById('dnd');
if (dnd_wrapper) {

  var dnd_rows = dnd_wrapper.getElementsByTagName('li');

  var drag; // Функция управления drag
  var objChildren, tChList; // под пункты (дети) текущего li и временный контейнер для них
  
  hookEvent(d.body, 'mouseup', dragStop); // событие отпускания (drop) элемента


  // Обрабатываем элементы списка dnd
  for (var i = 0; i < dnd_rows.length; i++) {
      var dnd_block = dnd_rows[i];
      dnd_block.className = 'dnd_block';
      dnd_block.setAttribute('row_ind', i);
    
    var level;
    if (level = dnd_rows[i].getAttribute('level')) {
      dnd_rows[i].style.marginLeft = 20*level + 'px';
    }
    
    setDragElement(dnd_block); // создаем элемент, за который перетягивать пункты списка    
  }
  
  var listOffsetLeft = dnd_wrapper.offsetLeft; // отступ списка от края экрана
  var rowHeightMargin = Math.round(dnd_wrapper.offsetHeight/dnd_rows.length);
  
  var dndUls = dnd_wrapper.getElementsByTagName('ul');
  var treeList = dndUls.length;
  if(treeList)
    var lvMargin = dndUls[0].offsetLeft - listOffsetLeft;
}

var draging = false; // перетаскиваемый обьект / указатель на активность процесса перетаскивания

/***********************
* Создаем элемент, за который мы будем тянуть блок
***********************/
function setDragElement(dnd_block) {
  var dnd_drager = d.createElement('div');
  dnd_drager.className = 'dnd_drager';
  dnd_drager.onmousedown = function(e) {dragStart(dnd_block, e); return false;};
  dnd_block.insertBefore(dnd_drager, dnd_block.firstChild);
}

/***********************
* Начало перетаскивания
***********************/
function dragStart(obj, e) { 
  draging = obj;
  var row_ind = obj.getAttribute('row_ind') * 1; // индекс взятой строки
  var cur_ind = row_ind; // текущий индекс взятой строки
  
  // Узнаем точку отсчета, в которой был захвачен dnd_block
  e = fixEvent(e);
  var stCoords = {x:e.pageX, y:e.pageY};
  var curCoords = {x:e.pageX, y:e.pageY}; // Координаты точки относительно которой считается смещение элемента
  var lastCoords = {x:e.pageX, y:e.pageY};
  var lastObj = dnd_wrapper; // Прошлый ul (нужно для организации подсветки активного уровня
  var tdiv = d.createElement('div'); 
  d.body.appendChild(tdiv);  

  objChildren = nextTag(obj); // подкатегории (дети) текущего li. Используеться если нужно тащить сразу li и его детей
  if (objChildren && objChildren.tagName.toLowerCase() == 'ul') {
    var objChildrenPos = getPosition(objChildren);
    objChildrenPos = {x: objChildrenPos.x - e.pageX - lvMargin, y: objChildrenPos.y - e.pageY};
    tChList = d.createElement('ul');
    tChList.appendChild(objChildren);
    tChList.id = 'dnd';
    with(tChList.style) {
      position = 'absolute';
      zIndex = 999;
      top =  e.pageY + objChildrenPos.y + 'px';
      left = e.pageX + objChildrenPos.x + 'px';
    }
    d.body.appendChild(tChList);  
  }else objChildren = false;
  
  with(obj.style) {
    position = 'relative';
    zIndex = 999;
    if (objChildren) marginBottom = objChildren.offsetHeight + 'px';
  }

  drag = function(e)
    {
      e = fixEvent(e);
      var delta = {x:e.pageX - lastCoords.x, y:e.pageY - lastCoords.y};
      var direction = delta.y ? Math.abs(delta.y) / delta.y : delta.y; // знак, определяющий направление перетаскивания
      delta = {x:e.pageX - stCoords.x, y:e.pageY - stCoords.y};
      // Количество строк, на которые сместилась перетаскиваемая строка (тернарный оператор, что бы избавится от деления на 0)
      var sign = delta.y ? Math.abs(delta.y) / delta.y : delta.y; // знак смещения, относительно исходного положения перемещ. эл.

      var row_delta = Math.floor(Math.abs(delta.y) / rowHeightMargin) * sign; 
      
      var new_ind = row_ind + row_delta; // индекс места, в которое перетащили li (по вертикали)
      
      
      // Фиксим случай, когда row_delta получилась больше чем возможно
      // из-за чего у нас выходит рассинхронизация курсора и перетягиваемого элемента
      if (new_ind < 0) new_ind = 0;
      if (!dnd_rows[new_ind]) new_ind = dnd_rows.length - 1;
      
      if (new_ind != cur_ind) { // перемещаем li по DOM (сортировка, изменение положения по вертикали в иерархии)
        var curUl = obj.parentNode;
        var curOffset = curUl.offsetLeft; // смещение по оси x

        // задаем параметры для перемещения li и перемещаем его
        var rowAfter = (direction < 1)?dnd_rows[new_ind - 1]:dnd_rows[new_ind];
        if (rowAfter)
          insertAfter(obj, rowAfter);
        else //вставляем li в начало списка
          dnd_wrapper.insertBefore(obj, dnd_rows[0]);

        // если ul из которого был взят li пуст, удаляем его 
        if (!curUl.childNodes.length) curUl.parentNode.removeChild(curUl);
        
        //обновляем координату y начальной точки (фиксим положение перетаскиваемого Li относительно курсора)
        var amountOfSkippedRows = Math.abs(new_ind - cur_ind);
        curCoords.y = curCoords.y + rowHeightMargin * amountOfSkippedRows * direction;     
        
        curOffset = obj.parentNode.offsetLeft - curOffset; // сдвиг элемента по горизонтали, который меняется, если элемент перетаскивается между разными уровнями в списке
        curCoords.x += curOffset;

        cur_ind = new_ind;  
      }
      
      if (treeList) // Только для многоуровневых списков (сортировка по горизонтали в иерархии)      
      { 
        // Считаем диапазон возможных уровней
        var lvTop = dnd_rows[cur_ind - 1];
        lvTop = (lvTop && lvTop.parentNode.id != 'dnd_wrapper')?lvTop.parentNode.getAttribute('level'):0;
        var lvBot = dnd_rows[cur_ind + 1];
        lvBot = (lvBot && lvBot.parentNode.id != 'dnd_wrapper')?lvBot.parentNode.getAttribute('level'):0;
        
        var curLv = obj.parentNode.getAttribute('level');
        var cursorLv = Math.floor((e.pageX - listOffsetLeft) / lvMargin); //над каким уровнем сейчас находится курсор
        
        lvBot *= 1; lvTop *= 1; curLv *= 1; // конвертируем строчки в циферки
        
        // Ограничиваем допустимые уровни
        if (cursorLv < lvBot) cursorLv = lvBot;
        if (cursorLv > lvTop + 1) cursorLv = lvTop + 1;
        
        //d.getElementById('test').innerHTML = 'curLv: ' + curLv + ' cursorLv: ' + cursorLv + ' lvTop: ' + lvTop + ' lvBot: ' + lvBot + ' cur_ind:' + cur_ind +dnd_rows[cur_ind - 1];
        
        if (curLv > cursorLv) { // уменьшаем уровень li
          levelDown(obj, curLv, cursorLv);
        }else if (curLv < cursorLv && dnd_rows[cur_ind - 1]) { // нужно создать новый уровень (у первого li в списке уровень менять нельзя)   
          levelUp(obj, curLv, cursorLv);
        }

        // Делаем подсветку текущего уровня, делая прозрачными все остальные
        if (lastObj) //Возможно этот Ul уже удален из DOM, потому проверяем, можно ли менять его class
          lastObj.className = ''; // снимает класс, который выделял ul, в котором раньше был li
        
        lastObj = obj.parentNode;
        lastObj.className = 'highlight';
        dnd_wrapper.className = (dnd_wrapper === lastObj)?'highlight':'shadow';
      }

      // Собственно теперь можно поменять положение li относительно курсора, используя посчитанные координаты
      var move = {x:e.pageX - curCoords.x, y:e.pageY - curCoords.y};

      with(obj.style) {
        top = move.y + 'px';
        left = move.x + 'px';
      }
      
      if (tChList) { // список детей (при переносе li с детьми)
        with(tChList.style) {
          top =  e.pageY + objChildrenPos.y + 'px';
          left = e.pageX + objChildrenPos.x + 'px';
        }
      }
      
      lastCoords = {x:e.pageX, y:e.pageY}; // Координаты на которых закончилось выполнение функции

      return false;
    }
  hookEvent(d, 'mousemove', drag);
  
  // отменить перенос и выделение текста при клике на тексте
  document.ondragstart = function() { return false };
  document.body.onselectstart = function() { return false };
  
  /***********************
  * Уменьшает уровень li
  ***********************/
  function levelDown(obj, curLv, cursorLv) {
    while (curLv > cursorLv) {
      var afterUl = obj.parentNode;
      
      // afterUl - бывший родитель obj, после него мы вставляем obj
      insertAfter(obj, afterUl);
      
      // Если afterUl пустой, удаляем его
      if (!afterUl.childNodes.length) afterUl.parentNode.removeChild(afterUl);
      
      // редактируем координаты начальной точки
      curCoords.x -= lvMargin;
      
      curLv--;
    }
  }

  /***********************
  * Увеличивает уровень li
  ***********************/
  function levelUp(obj, curLv, cursorLv) {
    while (curLv < cursorLv) {
      ++curLv;
      // Вставляем либо в соседний ul нужного уровня, либо создаем новый ul
      var childUl = obj.previousSibling;
      if (childUl && childUl.getAttribute('level') == curLv) var newLv = childUl; // вставляем в существующий ul (находится више li)
      else if ((childUl = obj.nextSibling) && childUl.getAttribute('level') == curLv) var newLv = childUl; // вставляем в существующий ul (находится ниже li)
      else { //создаем новый ul
        var newLv = d.createElement('ul');
        
        newLv.setAttribute('level', curLv); // к curLv мы уже 1 прибавили в условном операторе выше
        
        // Вставляем новый список после obj
        obj.parentNode.insertBefore(newLv, obj);
      }

      // Вставляем obj в Ul
      if (obj.nextSibling && obj.nextSibling.getAttribute('level') == curLv) // если следующий li существует и в добавок на том же уровне, вставляем наш obj перед ним
        newLv.insertBefore(obj, newLv.firstChild);
      else
        newLv.appendChild(obj);
      
      // редактируем координаты начальной точки
      curCoords.x += lvMargin;
    }
  }
}


/***********************
* Событие при отпускании кнопки мышки
***********************/
function dragStop() {
  if(!draging) return false;
  
  var obj = draging; // li
  
  unhookEvent(d, 'mousemove', drag);
  document.ondragstart = null;
  document.body.onselectstart = null;
  draging = false;
  obj.style.position = 'static';
  obj.style.top = obj.style.left = 'auto';
  
  if (objChildren) { // возвращаем детей на место (если перетаскивался li вместе с детьми)
    // ровняем level детей
    
    var parentNewLvl = obj.parentNode.getAttribute('level') * 1; // уровень, на который перетащили li
    var childslv = objChildren.getAttribute('level') * 1;
    var lvldelta = parentNewLvl - childslv + 1;
    objChildren.setAttribute('level', childslv + lvldelta);
    
    var objChildrenUls = objChildren.getElementsByTagName('ul');
    for (var i = 0; i < objChildrenUls.length; i++) {
      objChildrenUls[i].setAttribute('level', objChildrenUls[i].getAttribute('level') * 1 + lvldelta);
    }
    
    insertAfter(objChildren, obj);
    d.body.removeChild(tChList);
    objChildren = tChList = false;
    
    // Убираем margin, который покрывал место занимаемое детьми
    obj.style.marginBottom = rowHeightMargin - obj.offsetHeight + 'px';
  }
  
  // Отключаем прозрачность элементов списка
  d.getElementById('dnd').className = '';
  obj.parentNode.className = '';
  
  ev.cast('dndDragStop',obj);
}

/***********************
* Обновление порядка элементов в списке
***********************/
ev.attach('dndDragStop', function(args) {
  var obj = args[1];
  // Находим индекс строки, которую мы тянули 
  for(var i = 0; i < dnd_rows.length; i++)
    if(dnd_rows[i] === obj) break;
      
      
  if(i==0)
    var sindex_new = 0;
  else
  {
    //Ищем li, у которого возьмем индекс
    var sindexDonor = dnd_rows[i];
    
    if (dnd_rows[i] === dnd_rows[i].parentNode.firstChild) // это первый li в списке(ul) подкатегорий, потому нам нужен индекс li, перед этим ul
      sindexDonor = dnd_rows[i].parentNode;
    
    // получаем обьект предыдущего li (в случае многоуровневого списка: li который находится на том же уровне)  
    // если брать тупо из dnd_rows, то может получится рассинхронизация индексов
    // так как возможна такая структура
    // li sindex=1
    // ul level=1 li sindex=10
    //            li sindex=11
    do
    {
      sindexDonor = (sindexDonor.previousSibling)?sindexDonor.previousSibling:sindexDonor.parentNode;
    }while (sindexDonor.tagName.toLowerCase() != 'li');
    
    var sindex_new = sindexDonor.getAttribute('sindex')*1 + 1;
  }
  
  var id = obj.id.replace('row_', ''); // выделяем id
  var sindex_old = obj.getAttribute('sindex'); // старый индекс строки
  
  var sign = sindex_old - sindex_new;
  sign = sign ? Math.abs(sign) / sign : sign; // +-1, число, на которое будет менятся sindex в заданом промежутке

  
  // если li перетащили вниз, то нам нужно подправить его новый индекс, так как он вставляется не после, а вместо предыдущего пункта (т.к. все пункты смещаются на одну позицию вверх)
  if(sign < 0) sindex_new += sign;
  
  var smin = Math.min(sindex_new, sindex_old);
  var smax = Math.max(sindex_new, sindex_old);
  
  for(var i = 0; i < dnd_rows.length; i++) 
  {
    var sindex = parseInt(dnd_rows[i].getAttribute('sindex'))*1;
    if (sign != 0) // если элемент перемещался
      if (sindex >= smin && sindex <= smax) // Если индекс входит в промежуток, в котором нужно обновить индексы, редактируем его
      {
        dnd_rows[i].setAttribute('sindex', sindex + sign);
      }
    dnd_rows[i].setAttribute('row_ind', i);
  }
  
  obj.setAttribute('sindex', sindex_new);
  
  ajax(location.href, {ajax: 1, ajaxAction: 'setsindex', id: id, sindexold: sindex_old, sindexnew: sindex_new});
},'sort');

/***********************
* Обновление id родителя для перетаскиваемого li
***********************/
ev.attach('dndDragStop', function(args) {
  var obj = args[1];
  if (treeList) { 
  // если это многоуровневый список, отправляем запрос, для изменения родителя перетаскиваемого li
    var ul = obj.parentNode;
    var id = obj.id.replace('row_', ''); // выделяем id
    if (ul.tagName.toLowerCase() == 'ul' && ul.id != 'dnd') {
    // это дочерняя категория, узнаем id родителя
      //var row = ul.previousSibling.getAttribute('row_ind')*1;
      var parentId = ul.previousSibling.id.replace('row_', '');
    }else{
      parentId = 0; // перемещаем категорию в корень
    }
    ajax(location.href, {ajax: 1, ajaxAction: 'setparent', id: id, parentid: parentId});
  }
},'setParent');

/***********************
* Проверка, присутстсвует в массиве элемент
***********************/
//Проверяет содержится ли значение в массиве/объекте
inObject=inArray=function(obj, value, flEqualityType){
/*
Параметры:
- obj - объект или массив значений в котором ищем
- value - искомое значение
- flEqualityType - (необязательный) - флаг сравнения типов [true | false]
(по умолчанию false - сравнение без типов данных)
*/
  var obj=obj, value=value, flEqualityType=flEqualityType || false;
  if(!obj || typeof(obj)!='object') return false;
  for(var i in obj){
  if(flEqualityType===true && obj[i]===value) return true;
  if(flEqualityType===false && obj[i]==value) return true;
  }
  return false;
};

/***********************
* Передает следующий тег
***********************/
function nextTag(obj) {
  if (!obj.nextSibling) return false;
  obj = obj.nextSibling;
  if (obj.tagName == undefined) obj = nextTag(obj);
  return obj;
}

/***********************
* Передает первый тег
***********************/
function firstTag(obj) {
  obj = obj.firstChild;
  while(obj.tagName == undefined) {
    obj = obj.nextSibling;
  }
  return obj;
}

/***********************
* Вставляет obj после after
***********************/
function insertAfter(obj, after) {
  if (after.nextSibling)
    after.parentNode.insertBefore(obj, after.nextSibling);
  else // после after нету node, потому вставляем наш элемент в конец родителя
    after.parentNode.appendChild(obj);
}

/***********************
* Кросбраузерное исправление события event
***********************/
function fixEvent(e) {
	// получить объект событие для IE
	e = e || window.event

	// добавить pageX/pageY для IE
	if ( e.pageX == null && e.clientX != null ) {
		var html = document.documentElement
		var body = document.body
		e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0)
		e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0)
	}

	// добавить which для IE
	if (!e.which && e.button) {
		e.which = e.button & 1 ? 1 : ( e.button & 2 ? 3 : ( e.button & 4 ? 2 : 0 ) )
	}

	return e
}

/***********************
* Координаты левого верхнего угла элемента
***********************/
function getPosition(e){
  var left = 0
  var top  = 0

  while (e.offsetParent){
    left += e.offsetLeft
    top  += e.offsetTop
    e    = e.offsetParent
  }

  left += e.offsetLeft
  top  += e.offsetTop

  return {x:left, y:top}
}

/***********************
* Получить сдвиг target относительно курсора мыши
***********************/
function getMouseOffset(target, e) {
  var docPos  = getPosition(target)
  return {x:e.pageX - docPos.x, y:e.pageY - docPos.y}
}


/***********************
* Прикрепить событие
***********************/
 function hookEvent(element, eventName, callback)
{
    if(typeof(element) == "string")
        element = document.getElementById(element);
    if(element == null)
        return;
    if(element.addEventListener)
    {
        if(eventName == 'mousewheel')
        {
            element.addEventListener('DOMMouseScroll',
            callback, false);
        }
        element.addEventListener(eventName, callback, false);
    }
    else if(element.attachEvent)
        element.attachEvent("on" + eventName, callback);
}

/***********************
* Открепить событие
***********************/
function unhookEvent(element, eventName, callback)
{
    if(typeof(element) == "string")
        element = document.getElementById(element);
    if(element == null)
        return;
    if(element.removeEventListener)
    {
        if(eventName == 'mousewheel')
        {
            element.removeEventListener('DOMMouseScroll',
            callback, false);
        }
            element.removeEventListener(eventName, callback, false);
    }
    else if(element.detachEvent)
        element.detachEvent("on" + eventName, callback);
}

/***********************
* Отмена всплывания событий
***********************/
function cancelEvent(e)
{
    e = e ? e : window.event;
    if(e.stopPropagation)
        e.stopPropagation();
    if(e.preventDefault)
        e.preventDefault();
    e.cancelBubble = true;
    e.cancel = true;
    e.returnValue = false;
    return false;
}


/***********************
* Снимает галочки с checkbox и чистит значения input
***********************/
function resetFields(obj) {
  var inputs = obj.getElementsByTagName('input');
  var options = obj.getElementsByTagName('select');
  for (var i=0; i < inputs.length; i++) {
    if (inputs[i].type == 'checkbox') {
      inputs[i].checked = false;
    }
    if (inputs[i].type == 'text') {
      inputs[i].value = '';
    }
  }
  for (var i=0; i < options.length; i++) {
    options[i].selectedIndex = 0;
  }
}

/***********************
* Фильтрация ввода кода для вставки блока
***********************/
function filterCode(obj) {
  obj.value = obj.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
}

/*******************
 *
 * AJAX
 *
 ******************/

/***********************
* Отображает анимацию загрузки
***********************/
function startLoad() {
  if (d.getElementById('loading_layer')) return;
  // Блокируем все submit
  $('form input[type="submit"]').prop('disabled', 'disabled');
  
  var loading_layer = d.createElement('div');
  loading_layer.id = 'loading_layer';
  loading_layer.className = 'ajax_loading';
  d.body.appendChild(loading_layer);
}

/***********************
* Прекращает анимацию загрузки
***********************/
function stopLoad() {
  if (!d.getElementById('loading_layer')) return;
  d.body.removeChild(d.getElementById('loading_layer'));
  
  // Разблокироваем все submit
  $('form input[type="submit"]').removeProp('disabled');
}

function successLoad(content, type) {
  ajax_block(type, content);
}

function ajax(url, data, func)
{
    //Запускаем анимацию загрузки
    
    startLoad();
    jQuery.ajax({
      url: url,
      data: data,
      success: function(answ)
      {
        if(func){func(answ); return;}
        if(/function *[^\(]+\([^\)]*\) *\{/.test(data))data(answ);
      },
      error: function(xhr, textStatus, errorThrown){alert(xhr.responseText)},
      complete: stopLoad
    });
}

function var_dump(obj)
{
  var dump = '';
  for (prop in obj)
  {
    dump += 'prop: ' + prop + '\n val: ' + obj[prop] + '\n\n';
    //парсим вложенный обьект
    /*if (typeof(obj[prop]) == 'object')
      dump += '\n   ===' + var_dump(obj[prop]);*/
  }
  return dump;
}

/*******************
 *
 * Вспомагательные функции
 *
 ******************/
function centerBlock(id) {
  var block = d.getElementById(id);
  var bWidth = block.offsetWidth;
  var bHeight = block.offsetHeight;

  var body = document.body
  var docElem = document.documentElement
  var clientTop = docElem.clientTop || body.clientTop || 0;
  
  block.style.top = Math.round(clientTop + (d.body.offsetHeight - bHeight) / 2) + 'px';
  block.style.left = Math.round((d.body.offsetWidth - bWidth) / 2) + 'px';
}
 
function getElementsByClass(searchClass,node,tag)
{
    var classElements = new Array();
    if ( node == null )
        node = d;
    if ( tag == null )
        tag = '*';
    var elements = node.getElementsByTagName(tag);
    var elemLength = elements.length;
    var pattern = new RegExp('(^|\\\\s)'+searchClass+'(\\\\s|$)');
    for (var i = 0, j = 0; i < elemLength; i++) {
        if ( pattern.test(elements[i].className) ) {
            classElements[j] = elements[i];
            j++;
        }
    }
    return classElements;
}

function getOffsetRect(elem) {
    var box = elem.getBoundingClientRect()

    var body = document.body
    var docElem = document.documentElement

    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0

    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft

    return { top: Math.round(top), left: Math.round(left) }
}


$(function() {
  $('.gridLayout').each(function() {
    var maxHeight = 0;
    sameHeightChildren = $('article', this);
    sameHeightChildren.each(function() {
      maxHeight = Math.max(maxHeight, $(this).height());
    });
    sameHeightChildren.css({ height: maxHeight + 'px' });    
  });
});
