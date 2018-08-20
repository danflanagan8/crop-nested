(function ($, Drupal) {

  Drupal.behaviors.cropCoordinates = {
    attach: function (context, settings) {
      $('.crop-coord', context).each(function(){
        $img = $(this).find('img');
        $(this).css('width', $img.data('crop-width'));
        $(this).css('height', $img.data('crop-height'));
        $img.css('transform', 'translate(-' + $img.data('crop-x') + 'px, -' + $img.data('crop-y') + 'px)');
        $(this).click(function(){
          $(this).css('width', $img.width());
          $(this).css('height', $img.height());
          $img.css('transform', 'translate(0, 0)');
        });
        $(this).addClass('processed');
      });
    },
  }

})(jQuery, Drupal);
