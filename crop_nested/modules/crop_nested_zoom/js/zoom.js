(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.cropNestedZoom = {
    attach: function (context, settings) {
      var $cropNested = $('.crop-nested', context);
      if($cropNested.length){
        $cropNested.each(function(){
          }).on('mouseover mouseout', function(){
            $(this).find('img').toggleClass('zoom');
          });
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
