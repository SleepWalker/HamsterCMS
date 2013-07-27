/*
Social v 1.0
by Svaitoslav Danylenko - http://hamstercms.com

Licensed under the GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
- free for use in personal projects
- attribution requires leaving author name, author link, and the license info intact
*/

setTimeout(function() {
  if(!window.hSocialInit)
  {
    window.___gcfg = {                // Настройки языка для Google +1 Button
          lang: 'ru'
    };

    var apis = {
      gpApi: 'https://apis.google.com/js/plusone.js',                                            // Google +1 Button
      vkApi: '//vk.com/js/api/openapi.js?75',                                                    // Vkontakte API
      twApi: '//platform.twitter.com/widgets.js',                                                // Twitter Widgets
      fbApi: '//connect.facebook.net/en_US/all.js#xfbml=1',                                      // Facebook SDK
    },
    script   = 'script',

    fragment = document.createDocumentFragment(),
    element  = document.createElement(script),

    clone;

    // вставляем в дом скрипты для всех apis
    for (var id in apis)
    {
      clone = element.cloneNode(false);
      clone.async = true;
      clone.src = apis[id];
      clone.id = id;
      clone.type = 'text/javascript';
      fragment.appendChild(clone);
    }

    clone = document.getElementsByTagName(script)[0];
    clone.parentNode.insertBefore(fragment, clone);

    
    window.vkAsyncInit = function() {
      window.VK = VK; // на всякий случай
      $('body').trigger('vkInit');
    };

    window.hSocialInit = true;
  }
}, 200);

$.fn.hcomments = function(type)
{
  if(!$(this).data('initialized'))
  {
    $(this).data('initialized', true);
    VK.Widgets.Comments('vkcomments', {limit: 10, attach: '*'});
  }
};

$.hvklike = function(settings)
{
  var vkLikeInit = function() {
    window.VK.Widgets.Like('vklike', settings);
  };
  if($('#vklike').is(':empty'))
    if(window.VK === undefined)
    {
      $('body').off('vkInit', vkLikeInit);
      $('body').on('vkInit', vkLikeInit);
    }
    else
    {
      vkLikeInit();
    }
};

$(function() {
  $('body').on('click', '.HTabs > a', function () {
    $tabsLinks = $(this).parent().children();
    $tabsContent = $(this).parents('.HTabsContainer').children('section');
    $tabsLinks.removeClass('active');
    $(this).addClass('active');

    if($(this).data('index') === undefined)
    {
      $tabsLinks.each(function(index) {
        $(this).data('index', index); 
      });      
    }

    var index = $(this).data('index');
    if($tabsContent.eq(index).is(':visible')) return false;
    //$('html, body').animate({scrollTop:$(this).offset().top}, 'slow'); 
    $tabsContent.hide('normal');
    $tabsContent.eq(index).show('normal');

    return false;
  });
});
