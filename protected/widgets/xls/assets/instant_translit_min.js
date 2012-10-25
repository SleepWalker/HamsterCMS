﻿/*
Translit

Created by SleepWalker
www.udf.su
*/

﻿
(function($){$.fn.translit=function(settings){ru_to_en={'А':'a','Б':'b','В':'v','Г':'g','Д':'d','Е':'e','Ж':'j','З':'z','И':'i','Й':'y','К':'k','Л':'l','М':'m','Н':'n','О':'o','П':'p','Р':'r','С':'s','Т':'t','У':'u','Ф':'f','Х':'h','Ц':'ts','Ч':'ch','Ш':'sh','Щ':'sch','Ъ':'','Ы':'yi','Ь':'','Э':'e','Ю':'yu','Я':'ya','а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ж':'j','з':'z','и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'h','ц':'ts','ч':'ch','ш':'sh','щ':'sch','ъ':'y','ы':'yi','ь':'','э':'e','ю':'yu','я':'ya',' ':'_','.':'',',':''};var transliteratedField=this;this.on('blur',function()
{var value=$(this).prop('value');$(this).prop('value',strTranslit(value));});var link=$('<a href="" class="icon_refresh"></a>');link.on('click',function()
{var value=$(transliteratedField.parents('div')[0]).prevAll('div').children('input[type="text"]').prop('value');transliteratedField.prop('value',strTranslit(value));return false;});link.insertAfter(this);};function strTranslit(str)
{str=str.toLowerCase();var newStr='';for(var i=0;i<str.length;i++)
{if(array_key_exists(str[i],ru_to_en))
newStr+=ru_to_en[str[i]];else
newStr+=str[i];}
return newStr;}
function array_key_exists(key,search){if(!search||(search.constructor!==Array&&search.constructor!==Object))
{return false;}
return search[key]!==undefined;}})(jQuery);
