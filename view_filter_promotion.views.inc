<?php

/**
 * Implements hook_views_data_alter().
 */
function view_filter_promotion_views_data_alter(array &$data) {
  if (!empty($data['commerce_product_field_data'])) {
    $data['commerce_product_field_data']['view_filter_promotion_product_promotion'] = [
      'title' => t('Promotion/discount'),
      'group' => t('promotion'),
      'filter' => [
        'field' => 'product_id',
        'id' => 'view_filter_promotion',
        'title' => t('Product has promotion'),
        'help' => t(' allows you to filter products that may have a valid promotion')
      ]
    ];
  }
}