<?php

namespace Drupal\crop_nested\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\crop\Entity\CropType;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget;

/**
 * Plugin implementation of the 'image_widget_crop_nested' widget.
 *
 * @FieldWidget(
 *   id = "image_widget_crop_nested",
 *   label = @Translation("ImageWidget crop (nested)"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageCropWidgetNested extends ImageCropWidget {

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

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'nest' => '',
      'egg' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    if(!$crop_types_options = $this->imageWidgetCropManager->getAvailableCropType(CropType::getCropTypeNames())){
      return $element;
    }
    $element['nest'] = [
      '#title' => $this->t('Crop Nest'),
      '#type' => 'select',
      '#options' => $crop_types_options,
      '#default_value' => $this->getSetting('nest'),
      '#multiple' => FALSE,
      '#required' => FALSE,
      '#description' => $this->t('The larger crop for which nesting will be validated. This crop will be required.'),
    ];
    $element['egg'] = [
      '#title' => $this->t('Crop Egg'),
      '#type' => 'select',
      '#options' => $crop_types_options,
      '#default_value' => $this->getSetting('egg'),
      '#multiple' => FALSE,
      '#required' => FALSE,
      '#description' => $this->t('The smaller crop for which nesting will be validated. This crop will be required.'),
    ];

    return $element;
  }

  public function validateNested(array $element, FormStateInterface $form_state) {
    $images = $form_state->getValue($element['#field_name']);
    foreach ($images as $image) {
      if(!$image['#value']){
        continue;
      }
      $crops = $image['image_crop']['crop_wrapper'];
      $nest = $crops[$element['#nest']]['crop_container']['values'];
      $egg = $crops[$element['#egg']]['crop_container']['values'];

      if($nest['crop_applied'] == 0){
        $form_state->setErrorByName($element['#field_name'], t('Please select a @nest crop.', ['@nest' => $element['#nest']]));
        continue;
      }
      if($egg['crop_applied'] == 0){
        $form_state->setErrorByName($element['#field_name'], t('Please select a @egg crop.', ['@egg' => $element['#egg']]));
        continue;
      }
      //These coordinates are TOP LEFT! Not the center!
      if($egg['x'] < $nest['x']){
        $form_state->setErrorByName($element['#field_name'], t('@egg crop must be nested inside @nest crop.', ['@egg' => $element['#egg'], '@nest' => $element['#nest']]));
        continue;
      }
      if($egg['y'] < $nest['y']){
        $form_state->setErrorByName($element['#field_name'], t('@egg crop must be nested inside @nest crop.', ['@egg' => $element['#egg'], '@nest' => $element['#nest']]));
        continue;
      }
      if($egg['x'] + $egg['width'] > $nest['x'] + $nest['width']){
        $form_state->setErrorByName($element['#field_name'], t('@egg crop must be nested inside @nest crop.', ['@egg' => $element['#egg'], '@nest' => $element['#nest']]));
        continue;
      }
      if($egg['y'] + $egg['height'] > $nest['y'] + $nest['height']){
        $form_state->setErrorByName($element['#field_name'], t('@egg crop must be nested inside @nest crop.', ['@egg' => $element['#egg'], '@nest' => $element['#nest']]));
        continue;
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @return array[]
   *   The form elements for a single widget for this field.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Add properties needed by process() method.
    $element['#nest'] = $this->getSetting('nest');
    $element['#egg'] = $this->getSetting('egg');

    return parent::formElement($items, $delta, $element, $form, $form_state);
  }

}
