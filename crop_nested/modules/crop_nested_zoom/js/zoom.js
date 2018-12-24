(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.cropNestedZoom = {
    attach: function (context, settings) {
      var $cropNested = $('.crop-nested', context);
      if($cropNested.length){
        $cropNested.each(function(){
          Drupal.behaviors.cropNestedZoom.addAttributes($(this));
          }).on('mouseover mouseout', function(){
            $(this).find('img').toggleClass('zoom');
          });
      }
    },
    addAttributes: function($cropNestedElem){
      var $img = $cropNestedElem.find('img');
      //scale determines how much the image style scaled the image
      var scale = $img.attr('width') / Number($img.data('nest-width'));
      var zoom = Number($img.data('nest-width')) / Number($img.data('egg-width'));
      var xTrans = zoom * $img.data('egg-x') / $img.data('nest-width') * 100;
      var yTrans = zoom * $img.data('egg-y') / $img.data('nest-height') * 100;
      //zoom relates nest crop size to egg crop size.
      var translate = 'translate(-' + xTrans + '%, -' + yTrans + '%)';
      $img.css('transform', translate + 'scale(' + zoom + ')');
      $cropNestedElem.css('max-width', $img.attr('width') + 'px');
    },
  };
})(jQuery, Drupal, drupalSettings);
