<?php

namespace Drupal\crop_nested_zoom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crop_nested\Plugin\Field\FieldFormatter\CropNestedFormatter;

/**
 * Plugin implementation of the 'image_crop_nested_zoom' formatter.
 * Prints a fancy image tag leveraging nested crops.
 *
 * @FieldFormatter(
 *   id = "image_crop_nested_zoom",
 *   label = @Translation("Zoom"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class CropNestedZoom extends CropNestedFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'transition_time' => '1000',
      'begin_zoomed' => false,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['transtion_time'] = [
      '#title' => t('Transition Time (ms)'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('transition_time'),
      '#description' => t('The time (ms) it takes to complete the zoom.'),
    ];
    $element['begin_zoomed'] = [
      '#title' => t('Begin Zoomed In'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('begin_zoomed'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach($elements as &$element){
      $element['#image']['#item_attributes']['style'][] = 'transition: transform ' . $this->getSetting('transition_time') / 1000 . 's';
      $element['#attached']['library'][] = 'crop_nested_zoom/zoom';
      if($this->getSetting('begin_zoomed')){
        $element['#image']['#item_attributes']['class'][] = 'zoom';
      }
    }
    return $elements;
  }

}
