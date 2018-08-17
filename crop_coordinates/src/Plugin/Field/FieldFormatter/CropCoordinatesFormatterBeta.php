<?php

namespace Drupal\crop_coordinates\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use \Drupal\Core\Field\EntityReferenceFieldItemListInterface;

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
    $elements = [];

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    if (empty($images = $this->getEntitiesToView($items, $langcode))) {
      // Early opt-out if the field is empty.
      return $elements;
    }
    $crop_storage = \Drupal::service('entity.manager')->getStorage('crop');
    $crop_types = $crop_storage->loadMultiple();
    $image_style = $this->imageStyleStorage->load('featured');
    foreach ($images as $delta => $image) {
      $image_uri = $image->getFileUri();
      $url = $image_style ? $image_style->buildUrl($image_uri) : file_create_url($image_uri);

      // Add cacheability metadata from the image.
      $cacheability = CacheableMetadata::createFromObject($image);

      $data = array(
        '#theme' => 'image_crop_coordinates',
        '#url' => $url,
        '#crop' => array(),
      );
      $crops = $crops;
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
      $data['#crop'] = array();
      $data['#crop']['x'] = $crops['thumb']['position']['x'] - $crops['featured']['position']['x'];
      $data['#crop']['y'] = $crops['thumb']['position']['y'] - $crops['featured']['position']['y'];
      $data['#crop']['width'] = $crops['thumb']['size']['width'];
      $data['#crop']['height'] = $crops['thumb']['size']['height'];

      $elements[$delta] = $data;

      $cacheability->applyTo($elements[$delta]);
    }
    return $elements;
  }

}
