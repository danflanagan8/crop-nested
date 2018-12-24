(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.cropNestedZoom = {
    attach: function (context, settings) {
      var $cropNested = $('.crop-nested', context);
      if($cropNested.length){
        $cropNested.each(function(){
          Drupal.behaviors.cropNestedZoom.addAttributes($(this));
          }).on('mouseover', function(){
            $(this).addClass('zoom')
          }).on('mouseout', function(){
            $(this).removeClass('zoom');
          });
        $(window).resize(function(){
          $cropNested.each(function(){
            Drupal.behaviors.cropNestedZoom.addAttributes($(this));
          });
        });
      }
    },
    addAttributes: function($cropNestedElem){
      var $img = $cropNestedElem.find('img');
      //scale determines how much the image style scaled the image
      var scale = $img.attr('width') / Number($img.data('nest-width'));
      //browserFactor relates image actual size to image natural size.
      var browserFactor = $img.width()/$img.attr('width');
      //zoom relates nest crop size to egg crop size.
      var zoom = Number($img.data('nest-width')) / Number($img.data('egg-width'));
      var translate = 'translate(-' + scale * browserFactor * $img.data('egg-x') + 'px, -' + scale * browserFactor * $img.data('egg-y') + 'px)';
      $img.css('transform', translate + 'scale(' + zoom + ')');
      $cropNestedElem.css('max-width', $img.attr('width') + 'px');
    },
  };
})(jQuery, Drupal, drupalSettings);
