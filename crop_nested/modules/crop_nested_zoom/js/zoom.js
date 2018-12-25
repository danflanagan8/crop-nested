(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.cropNestedZoom = {
    attach: function (context, settings) {
      var $cropNested = $('.crop-nested', context);
      if($cropNested.length){
        $cropNested.each(function(){
          //always add add zoom handlers
          $(this).on('zoom-in', function(){
            $(this).find('img').addClass('zoom');
          });
          $(this).on('zoom-out', function(){
            $(this).find('img').removeClass('zoom');
          });
          $(this).on('zoom-toggle', function(){
            $(this).find('img').toggleClass('zoom');
          });
          //add other events depending on configuration
          if($(this).hasClass('click')){
            if($(this).hasClass('toggle')){
              $(this).on('click', function(){
                $(this).trigger('zoom-toggle');
              });
            }else{
              $(this).on('click', function(){
                $(this).trigger('zoom-toggle');
                $(this).off('click');
              });
            }
          }

          if($(this).hasClass('mouse')){
            if($(this).hasClass('toggle')){
              $(this).on('mouseover', function(){
                $(this).trigger('zoom-toggle');
              });
              $(this).on('mouseout', function(){
                $(this).trigger('zoom-toggle');
              });
            }else{
              $(this).on('mouseover', function(){
                $(this).trigger('zoom-toggle');
                $(this).off('mouseover');
              });
            }
          }
          
        });
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
