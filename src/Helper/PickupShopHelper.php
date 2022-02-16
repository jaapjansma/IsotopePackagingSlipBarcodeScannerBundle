<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Krabo\IsotopePackagingSlipBarcodeScannerBundle\Helper;

use Contao\Backend;
use Contao\BackendUser;
use Contao\System;
use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\PickupShippingMethodEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PickupShopHelper {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
   */
  protected $container = null;

  public function __construct(ContainerInterface $container = null) {
    $this->container = $container;
  }

  /**
   * @return \Isotope\Model\Shipping[]
   */
  public function getPickupShopShippingMethods() {
    $event = new PickupShippingMethodEvent();
    $this->container->get('event_dispatcher')->dispatch($event, PickupShippingMethodEvent::EVENT_NAME);
    return $event->shippingMethods;
  }

  /**
   * @return \Isotope\Model\Shipping[]
   */
  public function getRestrictedPickupShopShippingMethods() {
    $shippingMethods = $this->getPickupShopShippingMethods();
    $user = BackendUser::getInstance();
    if ($user->id && !$user->restrict_pickup_shop) {
      return $shippingMethods;
    } elseif ($user->id) {
      $return = [];
      foreach($user->restricted_pickup_shops as $restricted_pickup_shop_id) {
        if (isset($shippingMethods[$restricted_pickup_shop_id])) {
          $return[$restricted_pickup_shop_id] = $shippingMethods[$restricted_pickup_shop_id];
        }
      }
      return $return;
    }
    return [];
  }

}