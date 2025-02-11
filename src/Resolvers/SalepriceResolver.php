<?php

namespace Drupal\commerce_product_saleprice\Resolvers;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_product_saleprice\Services\SalepriceService;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the Sale price resolver class.
 *
 * @package Drupal\commerce_product_saleprice\Resolvers
 */
class SalepriceResolver implements PriceResolverInterface {

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The saleprice service.
   *
   * @var \Drupal\commerce_product_saleprice\Services\SalepriceService
   */
  protected $salepriceService;

  /**
   * Constructs SalepriceResolver object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_product_saleprice\Services\SalepriceService $saleprice_service
   *   The saleprice service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SalepriceService $saleprice_service) {
    $this->config = $config_factory->get('commerce_product_saleprice.settings');
    $this->salepriceService = $saleprice_service;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $product_variation, $quantity, Context $context) {
    if ($this->salepriceService->isOnSale($product_variation)) {
      $discount_field = $this->config->get('discount_field');
      if (!$product_variation->get($discount_field)->isEmpty()) {
        $discount = $product_variation->get($discount_field)->first()->getValue();
        $price = $product_variation->getPrice();
        return $price->multiply((100 - ((float) $discount['value'])) / 100);
      }

      $saleprice_field = $this->config->get('saleprice_field');
      if (!$product_variation->get($saleprice_field)->isEmpty()) {
        return $product_variation->get($saleprice_field)->first()->toPrice();
      }

      return NULL;
    }

    return NULL;
  }

}
