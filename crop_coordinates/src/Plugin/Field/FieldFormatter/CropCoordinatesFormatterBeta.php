<?php

namespace Drupal\crop_coordinates\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use \Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'image_crop_coordinates' formatter.
 * Prints a fancy image tag
 *
 * @FieldFormatter(
 *   id = "image_crop_coordinates_beta",
 *   label = @Translation("Crop coordinates beta"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class CropCoordinatesFormatterBeta extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = ['Thumb to Featured exploder'];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $images = $this->getEntitiesToView($items, $langcode);

    $crop_storage = \Drupal::service('entity.manager')->getStorage('crop');
    $crop_types = $crop_storage->loadMultiple();
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
      //We calculate where the top-left corner of thumb sits relative to the
      //top-left corner of featured.
      $element['#item_attributes']['data-crop-x'][] = ($crops['thumb']['position']['x'] - 0.5 * $crops['thumb']['size']['width']) - ($crops['featured']['position']['x'] - 0.5*$crops['featured']['size']['width']);
      $element['#item_attributes']['data-crop-y'][] = ($crops['thumb']['position']['y'] - 0.5 * $crops['thumb']['size']['height']) - ($crops['featured']['position']['y'] - 0.5*$crops['featured']['size']['height']);
      $element['#item_attributes']['data-crop-width'][] = $crops['thumb']['size']['width'];
      $element['#item_attributes']['data-crop-height'][] = $crops['thumb']['size']['height'];
      $index += 1;

      $element['#image'] = $element;
      $element['#theme'] = 'image_crop_coordinates';
      $element['#attached']['library'][] = 'crop_coordinates/crop-coordinates';
    }

    return $elements;
  }

}
