<?php

namespace Drupal\view_filter_promotion;

use Drupal\views\Plugin\views\filter\BooleanOperator;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_order\PriceCalculator;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce\Context;

/**
 * Utilise le cache APCu afin d'accelerer la recherche.
 *
 * @author stephane
 *        
 */
class ViewFilterPromotionCache {
  
}