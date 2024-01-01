<?php

namespace Drupal\view_filter_promotion\Plugin\views\filter;

/**
 * Permet de filtrer les entites achetable (commerce_variation)
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("view_filter_promotion")
 */
class ViewFilterPromotion extends ViewFilterPromotionBase {
  
  /**
   * L'idée est de construire un tableau avec les entites qui peuvent etre
   * achetable.
   *
   * {@inheritdoc}
   * @see \Drupal\views\Plugin\views\filter\BooleanOperator::query()
   */
  function query() {
    if ($this->value) {
      $productsPromotion = $this->getItemsFromCache();
      if (!$productsPromotion) {
        $productsPromotion = [];
        /**
         *
         * @var \Drupal\views\Plugin\views\query\Sql $queryclone
         */
        $queryClone = clone $this->query;
        /**
         *
         * @var \Drupal\mysql\Driver\Database\mysql\Select $mysqlquery
         */
        $mysqlquery = $queryClone->query();
        $mysqlquery->innerJoin('commerce_product__variations', 'cpv', "cpv.entity_id = commerce_product_field_data.product_id");
        $mysqlquery->addField('cpv', 'variations_target_id', 'variation_id');
        $mysqlquery->range(NULL, NULL);
        $results = $mysqlquery->execute()->fetchAll(\PDO::FETCH_DEFAULT);
        $variationsIds = [];
        foreach ($results as $result) {
          $variationsIds[] = $result->variation_id;
        }
        $productsPromotion = [];
        if ($variationsIds) {
          $purchasable_entities = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->loadMultiple($variationsIds);
          foreach ($purchasable_entities as $variation_id => $purchasable_entity) {
            $result = $this->priceCalculator->calculate($purchasable_entity, 1, $this->getContext(), $this->getAjustements());
            $calculated_price = $result->getCalculatedPrice();
            $calculated_price_number = (int) $calculated_price->getNumber();
            $default_price = $result->getBasePrice();
            $default_price_number = (int) $default_price->getNumber();
            if ($calculated_price_number < $default_price_number) {
              $productsPromotion[] = $variation_id;
              continue;
            }
          }
          $this->setItemsFromCache($productsPromotion);
        }
      }
      if (!$productsPromotion) {
        $productsPromotion = [
          'none'
        ];
      }
      /**
       *
       * @var \Drupal\views\Plugin\views\query\Sql $query
       */
      $query = $this->query;
      $definition = [
        'table' => 'commerce_product__variations',
        'field' => 'entity_id',
        'left_table' => 'commerce_product_field_data',
        'left_field' => 'product_id'
      ];
      /**
       *
       * @var \Drupal\views\Plugin\views\join\Standard $join
       */
      $join = \Drupal::service('plugin.manager.views.join')->createInstance('standard', $definition);
      $query->addRelationship('cpv', $join, 'commerce_product');
      $query->addWhere('AND', 'cpv.variations_target_id', $productsPromotion, 'IN');
    }
  }
  
}