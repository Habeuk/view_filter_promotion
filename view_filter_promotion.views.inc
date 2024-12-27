<?php

/**
 * Implements hook_views_data_alter().
 */
function view_filter_promotion_views_data_alter(array &$data) {
  // \Stephane888\Debug\debugLog::DebugDrupal($data,
  // 'view_filter_promotion_views_data_alter',false,"/siteweb/lesroisdelareno/wb-horizon/web");
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
  foreach ($data as $table_name => $table_data) {
    if (str_contains($table_name, 'search_api_index_')) {
      $data[$table_name]['view_filter_promotion_search_api_index'] = [
        'title' => t('Promotion/discount'),
        'group' => t('promotion'),
        'filter' => [
          'field' => 'product_id',
          'id' => 'view_filter_promotion_search_api',
          'title' => t('Product has promotion'),
          'help' => t('Allows you to filter products that may have a valid promotion')
        ]
      ];
    }
  }
}