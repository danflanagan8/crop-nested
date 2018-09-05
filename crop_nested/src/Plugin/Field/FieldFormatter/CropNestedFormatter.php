<?php

namespace Drupal\crop_nested\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use \Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'image_crop_nested' formatter.
 * Prints a fancy image tag leveraging nested crops.
 *
 * @FieldFormatter(
 *   id = "image_crop_nested",
 *   label = @Translation("Nested Cropped Image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class CropNestedFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
   public function settingsSummary() {
     $summary = [];

     $image_styles = image_style_options(FALSE);
     // Unset possible 'No defined styles' option.
     unset($image_styles['']);
     // Styles could be lost because of enabled/disabled modules that defines
     // their styles in code.
     $image_style_setting = $this->getSetting('image_style');
     if (isset($image_styles[$image_style_setting])) {
       $summary[] = t('Nest style: @style', ['@style' => $image_styles[$image_style_setting]]);
     }
     else {
       $summary[] = t('Nest style: Original image');
     }
     if (isset($image_styles[$this->getSetting('egg_style')])) {
       $summary[] = t('Egg style: @style', ['@style' => $image_styles[$this->getSetting('egg_style')]]);
     }
     else {
       $summary[] = t('Egg style: Original image');
     }

     $link_types = [
       'content' => t('Linked to content'),
       'file' => t('Linked to file'),
     ];
     // Display this setting only if image is linked.
     $image_link_setting = $this->getSetting('image_link');
     if (isset($link_types[$image_link_setting])) {
       $summary[] = $link_types[$image_link_setting];
     }

     return $summary;
   }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'egg_style' => '',
      'image_link' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $element = parent::settingsForm($form, $form_state);
    $element['egg_style'] = [
      '#title' => t('Egg image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('egg_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => $image_styles,
      '#weight' => -1,
      '#description' => t('The Egg image style should be nested within the Nest image style.'),
    ];
    $element['image_style']['#weight'] = -2;
    $element['image_style']['#title'] = 'Nest image style';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $images = $this->getEntitiesToView($items, $langcode);

    $crop_storage = \Drupal::service('entity.manager')->getStorage('crop');
    $crop_types = $crop_storage->loadMultiple();
    $egg = $this->getSetting('egg_style');
    $nest = $this->getSetting('image_style');

    $index = 0;
    foreach($elements as &$element){
      $image = $images[$index];
      $image_uri = $image->getFileUri();
      foreach($crop_types as $crop_type){
        $crop = $crop_storage->getCrop($image_uri, $crop_type->bundle());
        if($crop){
         $crops[$crop_type->bundle()] = array(
           'size' => $crop->size(),
           'position' => $crop->position(),
         );
        }
      }
      //calculate where the thumb crop sits within the featured crop
      //The x and y values are the CENTER of the crop.
      //We calculate where the top-left corner of egg sits relative to the
      //top-left corner of nest.
      $element['#item_attributes']['data-crop-x'][] = ($crops[$egg]['position']['x'] - 0.5 * $crops[$egg]['size']['width']) - ($crops[$nest]['position']['x'] - 0.5 * $crops[$nest]['size']['width']);
      $element['#item_attributes']['data-crop-y'][] = ($crops[$egg]['position']['y'] - 0.5 * $crops[$egg]['size']['height']) - ($crops[$nest]['position']['y'] - 0.5 * $crops[$nest]['size']['height']);
      $element['#item_attributes']['data-crop-width'][] = $crops[$egg]['size']['width'];
      $element['#item_attributes']['data-crop-height'][] = $crops[$egg]['size']['height'];
      $index += 1;

      $element['#image'] = $element;
      $element['#theme'] = 'image_crop_nested';
      $element['#attached']['library'][] = 'crop_nested/crop-nested';
    }

    return $elements;
  }

}