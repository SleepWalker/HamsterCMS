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
    (function(d, s, id) {
      var div = d.createElement('div');
      div.id = 'fb-root';
      d.body.insertBefore(div, d.body.firstChild);
      var js, fjs = d.getElementsByTagName(s)[0]; 
      if (d.getElementById(id)) {return;} 
      js = d.createElement(s); js.id = id; 
      js.src = '//connect.facebook.net/en_US/all.js#xfbml=1';
      fjs.parentNode.insertBefore(js, fjs); 
    }(document, 'script', 'facebook-jssdk'));

    (function() {
      var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
      po.src = 'https://apis.google.com/js/plusone.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
    })();

    window.___gcfg = {
      lang: 'ru',
      parsetags: 'onload'
    };

    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');

    var vkTransport = document.createElement('div');
    vkTransport.id = 'vk_api_transport';
    document.body.appendChild(vkTransport);
    
    var oldVkInit = window.vkAsyncInit;
    window.vkAsyncInit = function() {
      if(oldVkInit) oldVkInit();
      if(document.getElementById('vklike'))
        VK.Widgets.Like('vklike', {type: 'vertical', height: 24}); 
        
      window.VK = VK; // на всякий случай
    };

      var el = document.createElement('script');
      el.type = 'text/javascript';
      el.src = 'http://vkontakte.ru/js/api/openapi.js';
      el.async = true;
      document.getElementById('vk_api_transport').appendChild(el);
      window.hSocialInit = true;
  }
}, 200);

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
