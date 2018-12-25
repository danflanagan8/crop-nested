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
      'trigger' => '',
      'toggle' => true,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
   public function settingsSummary() {
     $summary = parent::settingsSummary();
     $summary[] = $this->getSetting('transition_time') . 'ms';
     $summary[] = $this->getSetting('zoom_in') ? 'Zoom in' : 'Zoom out';
     $summary[] = 'Trigger: ' . $this->getSetting('trigger');
     $summary[] = $this->getSetting('toggle') ? 'Toggle' : 'No toggle';
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
    $element['trigger'] = [
      '#title' => t('Trigger'),
      '#type' => 'select',
      '#options' => array(
        'click' => 'Click',
        'mouse' => 'Hover',
      ),
      '#empty_option' => t('None'),
      '#default_value' => $this->getSetting('trigger'),
      '#description' => t('What triggers the zoom effect? If you select "None", presumably you will trigger with custom js.'),
    ];
    $element['toggle'] = [
      '#title' => t('Toggle Zoom'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('toggle'),
      '#description' => t('Can zoom in and out repeated based on trigger.'),
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
      //get natural width and height of the image after the image style
      //has been applied in order to set a max width on the crop-nested element.
      $url = $element['#image']['#url'];
      $dimensions = [
        'width' => $item->width,
        'height' => $item->height,
      ];
      $style->transformDimensions($dimensions, $url);
      $element['#attributes']['style'][] = 'max-width: ' . $dimensions['width'] . 'px;';

      //based on the nest and egg dimensions and positions, add some inline styles
      $zoom = $element['#image']['#item_attributes']['data-nest-width'][0] / $element['#image']['#item_attributes']['data-egg-width'][0];
      $x_trans = $element['#image']['#item_attributes']['data-egg-x'][0] / $element['#image']['#item_attributes']['data-egg-width'][0] * 100;
      $y_trans = $element['#image']['#item_attributes']['data-egg-y'][0] / $element['#image']['#item_attributes']['data-egg-height'][0] * 100;
      $translate = 'translate(-' . $x_trans . '%, -' . $y_trans . '%)';
      $element['#image']['#item_attributes']['style'][] = 'transition: transform ' . $this->getSetting('transition_time') / 1000 . 's;';
      $element['#image']['#item_attributes']['style'][] = 'transform: ' . $translate . ' scale('. $zoom .');';

      //add classes based on config
      if(!$this->getSetting('zoom_in')){
        $element['#image']['#item_attributes']['class'][] = 'zoom';
      }
      if($this->getSetting('toggle')){
        $element['#attributes']['class'][] = 'toggle';
      }
      if($this->getSetting('trigger')){
        $element['#attributes']['class'][] = $this->getSetting('trigger');
      }

      //a couple things that happen the same way every time
      $element['#attached']['library'][] = 'crop_nested_zoom/zoom';
      $element['#attributes']['class'][] = 'crop-nested-zoom';
    }
    return $elements;
  }

}
