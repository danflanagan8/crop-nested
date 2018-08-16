<?php

namespace Drupal\crop_coordinates\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use \Drupal\Core\Field\EntityReferenceFieldItemListInterface;

/**
 * Plugin implementation of the 'image_crop_coordinates' formatter.
 * Returns json encoded string containing url and crop data.
 *
 * @FieldFormatter(
 *   id = "image_crop_coordinates",
 *   label = @Translation("Crop coordinates"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class CropCoordinatesFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    unset($element['image_link']);
    unset($element['image_style']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = ['Displayed as JSON'];
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

    foreach ($images as $delta => $image) {
      $image_uri = $image->getFileUri();
      $url = file_create_url($image_uri);

      // Add cacheability metadata from the image.
      $cacheability = CacheableMetadata::createFromObject($image);

      $data = array(
        'url' => $url,
        'crops' => array(),
      );
      foreach($crop_types as $crop_type){
         $crop = $crop_storage->getCrop($image_uri, $crop_type->bundle());
         if($crop){
           $data['crops'][] = array(
             'type' => $crop_type->bundle(),
             'size' => $crop->size(),
             'position' => $crop->position(),
           );
         }
      }
      $elements[$delta] = ['#markup' => json_encode($data)];

      $cacheability->applyTo($elements[$delta]);
    }
    return $elements;
  }

}
