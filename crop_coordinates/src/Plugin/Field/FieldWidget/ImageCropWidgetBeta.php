<?php

namespace Drupal\crop_coordinates\Plugin\Field\FieldWidget;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Drupal\image_widget_crop\ImageWidgetCropInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\crop\Entity\CropType;
use Drupal\Core\Render\Element\Select;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget;

/**
 * Plugin implementation of the 'image_widget_crop_beta' widget.
 *
 * @FieldWidget(
 *   id = "image_widget_crop_beta",
 *   label = @Translation("ImageWidget crop beta"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageCropWidgetBeta extends ImageCropWidget {

  /**
   * Form API callback: Processes a crop_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   *
   * @return array
   *   The elements with parents fields.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);
    $element['#element_validate'][] = [static::class,'validateNested'];
    return $element;
  }

  public static function validateNested(array $element, FormStateInterface $form_state) {
    $images = $form_state->getValue($element['#field_name']);
    foreach ($images as $image) {
      $crops = $image['image_crop']['crop_wrapper'];
      $featured = $crops['featured']['crop_container']['values'];
      $thumb = $crops['thumb']['crop_container']['values'];

      if($featured['crop_applied'] == 0){
        $form_state->setErrorByName($element['#field_name'], t('Please select a Featured crop.'));
        continue;
      }
      if($thumb['crop_applied'] == 0){
        $form_state->setErrorByName($element['#field_name'], t('Please select a Thumb crop.'));
        continue;
      }
      //These coordinates are TOP LEFT! Not the center!
      if($thumb['x'] < $featured['x']){
        $form_state->setErrorByName($element['#field_name'], t('Thumb crop must be nested inside Featured crop. (left)'));
        continue;
      }
      if($thumb['y'] < $featured['y']){
        $form_state->setErrorByName($element['#field_name'], t('Thumb crop must be nested inside Featured crop. (top)'));
        continue;
      }
      if($thumb['x'] + $thumb['width'] > $featured['x'] + $featured['width']){
        $form_state->setErrorByName($element['#field_name'], t('Thumb crop must be nested inside Featured crop. (right)'));
        continue;
      }
      if($thumb['y'] + $thumb['height'] > $featured['y'] + $featured['height']){
        $form_state->setErrorByName($element['#field_name'], t('Thumb crop must be nested inside Featured crop. (bottom)'));
        continue;
      }
    }
  }

}
