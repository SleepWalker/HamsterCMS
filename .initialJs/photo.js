$(function(){
  // анимация header в photoGrid
  $('.photoGrid .block').each(function() {
    var header = $('header', this);
    var image = $('img', this);
    var titleDispos = header.outerHeight();
    var speed = 300;
    $(this).css({
      'background': '#121212',
      boxShadow: '0 0 50px 20px black inset',
    });
    $(this).hover(
      function()
      {
        header.animate({top: '-=' + titleDispos}, speed); 
        image.animate({opacity: 0.4}, speed);
      },
      function()
      {
        header.animate({top: '+=' + titleDispos}, speed); 
        image.animate({opacity: 1}, speed);
      }
    );
  });
});