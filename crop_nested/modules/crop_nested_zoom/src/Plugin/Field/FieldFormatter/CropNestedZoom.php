<?php

namespace Drupal\crop_nested_zoom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crop_nested\Plugin\Field\FieldFormatter\CropNestedFormatter;
use Drupal\image\Entity\ImageStyle;

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
      'zoom_in' => true,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
   public function settingsSummary() {
     $summary = parent::settingsSummary();
     $summary[] = $this->getSetting('transition_time') . 'ms';
     $summary[] = $this->getSetting('zoom_in') ? 'Zoom in' : 'Zoom out';
     return $summary;
   }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['transition_time'] = [
      '#title' => t('Transition Time (ms)'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('transition_time'),
      '#description' => t('The time (ms) it takes to complete the zoom.'),
    ];
    $element['zoom_in'] = [
      '#title' => t('Zoom In'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('zoom_in'),
      '#description' => t('As opposed to zoom out.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    /**
     * This function adds classes and inline styles so the zoom effect works.
     */
    $style = ImageStyle::load($this->getSetting('image_style'));

    foreach($elements as &$element){
      $item = $element['#item'];
      $url = $element['#image']['#url'];
      $dimensions = [
        'width' => $item->width,
        'height' => $item->height,
      ];
      $style->transformDimensions($dimensions, $url);
      $zoom = $element['#image']['#item_attributes']['data-nest-width'][0] / $element['#image']['#item_attributes']['data-egg-width'][0];
      $x_trans = $zoom * $element['#image']['#item_attributes']['data-egg-x'][0] / $element['#image']['#item_attributes']['data-nest-width'][0] * 100;
      $y_trans = $zoom * $element['#image']['#item_attributes']['data-egg-y'][0] / $element['#image']['#item_attributes']['data-nest-height'][0] * 100;
      $translate = 'translate(-' . $x_trans . '%, -' . $y_trans . '%)';
      $element['#image']['#item_attributes']['style'][] = 'transition: transform ' . $this->getSetting('transition_time') / 1000 . 's;';
      $element['#image']['#item_attributes']['style'][] = 'transform: ' . $translate . ' scale('. $zoom .');';
      $element['#attributes']['style'][] = 'max-width: ' . $dimensions['width'] . 'px;';
      $element['#attached']['library'][] = 'crop_nested_zoom/zoom';
      if(!$this->getSetting('zoom_in')){
        $element['#image']['#item_attributes']['class'][] = 'zoom';
      }
    }
    return $elements;
  }

}
