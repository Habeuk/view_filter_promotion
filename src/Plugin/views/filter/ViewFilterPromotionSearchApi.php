<?php

namespace Drupal\view_filter_promotion\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_order\PriceCalculator;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce\Context;
use Drupal\Core\Cache\ApcuBackendFactory;

/**
 * Permet de filtrer les entites achetable (commerce_variation)
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("view_filter_promotion_search_api")
 */
class ViewFilterPromotionSearchApi extends ViewFilterPromotionBase {
  
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
         * @var \Drupal\search_api\Query\Query $mysqlquery
         */
        $mysqlquery = $queryClone->query();
        $variationsIds = [];
        $resultsItems = $mysqlquery->execute()->getResultItems();
        foreach ($resultsItems as $item) {
          foreach ($item->getField('default_variation')->getValues() as $default_variation_id) {
            $variationsIds[] = $default_variation_id;
          }
        }
        
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
      
      if ($productsPromotion) {
        /**
         *
         * @var \Drupal\views\Plugin\views\query\Sql $query
         */
        $query = $this->query;
        $query->addWhere('AND', 'default_variation', $productsPromotion, 'IN');
      }
    }
  }
  
}