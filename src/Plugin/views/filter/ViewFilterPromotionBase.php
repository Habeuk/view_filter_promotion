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
use Drupal\Core\Cache\DatabaseBackendFactory;
use Drupal\Core\Form\FormStateInterface;

/**
 * Fichier de base permetttant de construire les filtres de promotion.
 */
class ViewFilterPromotionBase extends BooleanOperator {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;
  
  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;
  
  /**
   * The price calculator.
   *
   * @var \Drupal\commerce_order\PriceCalculatorInterface
   */
  protected $priceCalculator;
  /**
   *
   * @var \Drupal\Core\Cache\ApcuBackendFactory
   */
  protected $ApcuBackendFactory;
  
  /**
   *
   * @var \Drupal\Core\Cache\DatabaseBackendFactory
   */
  protected $DatabaseBackendFactory;
  /**
   *
   * @var \Drupal\commerce\Context
   */
  protected $context = null;
  
  /**
   *
   * @var \Drupal\Core\Cache\ApcuBackend
   */
  protected $cacheDatas;
  
  function __construct($configuration, $plugin_id, $plugin_definition, PriceCalculator $priceCalculator, AccountInterface $currentUser, CurrentStoreInterface $currentStore, ApcuBackendFactory $ApcuBackendFactory, DatabaseBackendFactory $DatabaseBackendFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->priceCalculator = $priceCalculator;
    $this->currentUser = $currentUser;
    $this->currentStore = $currentStore;
    $this->ApcuBackendFactory = $ApcuBackendFactory;
    $this->DatabaseBackendFactory = $DatabaseBackendFactory;
  }
  
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('commerce_order.price_calculator'), $container->get('current_user'), $container->get(
      'commerce_store.current_store'), $container->get('cache.backend.apcu'), $container->get('cache.backend.database'));
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->value_value = t('Available on promotion');
  }
  
  protected function getContext() {
    if (!$this->context) {
      $this->context = new Context($this->currentUser, $this->currentStore->getStore(), NULL, []);
    }
    return $this->context;
  }
  
  protected function getAjustements() {
    return [
      'promotion' => 'promotion'
    ];
  }
  
  /**
   *
   * @return \Drupal\Core\Cache\ApcuBackend
   */
  protected function getCacheACPu() {
    if (!$this->cacheDatas) {
      if (function_exists('apcu_cache_info')) {
        $this->cacheDatas = $this->ApcuBackendFactory->get($this->pluginId);
      }
      else {
        $this->cacheDatas = $this->DatabaseBackendFactory->get($this->pluginId);
      }
    }
    return $this->cacheDatas;
  }
  
  /**
   * --
   */
  public function deleteAllCache() {
    $this->getCacheACPu()->deleteAll();
  }
  
  /**
   *
   * @return array
   */
  protected function getItemsFromCache() {
    /**
     *
     * @var \Drupal\Core\Cache\ApcuBackend $cache
     */
    $cache = $this->getCacheACPu()->get($this->getKeyCid());
    return $cache->data ?? [];
  }
  
  /**
   *
   * @param array $productsPromotion
   */
  protected function setItemsFromCache(array $productsPromotion) {
    // Cache de 15 minutes par defaut.
    $this->getCacheACPu()->set($this->getKeyCid(), $productsPromotion, REQUEST_TIME + 900);
  }
  
  /**
   *
   * @return string
   */
  protected function getKeyCid() {
    return $this->view->id();
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\views\Plugin\views\filter\FilterPluginBase::valueSubmit()
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    parent::valueSubmit($form, $form_state);
    // after save value delete cache.
    $this->getCacheACPu()->delete($this->getKeyCid());
  }
}