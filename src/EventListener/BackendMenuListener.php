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

namespace Krabo\IsotopePackagingSlipBarcodeScannerBundle\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use Isotope\Model\Shipping;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

class BackendMenuListener {

  /**
   * @var \Symfony\Component\Routing\RouterInterface
   */
  protected $router;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
   */
  protected $container = null;

  public function __construct(RouterInterface $router, RequestStack $requestStack, ContainerInterface $container = null)
  {
    $this->router = $router;
    $this->requestStack = $requestStack;
    $this->container = $container;
  }

  /**
   * @ServiceTag("kernel.event_listener", event="contao.backend_menu_build", priority=-255)
   */
  public function onBackendMenuBuild(MenuEvent $event): void {
    $factory = $event->getFactory();
    $tree = $event->getTree();

    if ('mainMenu' !== $tree->getName()) {
      return;
    }

    $contentNode = $tree->getChild('isotope');

    $node = $factory
      ->createItem('isotopepackagingslipbarcodescanner_confirmstore')
      ->setUri($this->router->generate('isotopepackagingslipbarcodescanner_confirmstore'))
      ->setLabel($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirm_store'][0])
      ->setLinkAttribute('title', $GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirm_store'][1])
      ->setLinkAttribute('class', 'isotopepackagingslipbarcodescanner_confirmstore')
      ->setCurrent($this->requestStack->getCurrentRequest()->get('_controller') === 'isotopepackagingslipbarcodescanner_confirmstore')
    ;
    $contentNode->addChild($node);

    $pickupShops = $this->getAvailablePickupShops();
    foreach($pickupShops as $pickupShop) {
      $nodePickup = $factory
        ->createItem('isotopepackagingslipbarcodescanner_confirmshop_'.$pickupShop->id)
        ->setUri($this->router->generate('isotopepackagingslipbarcodescanner_confirmshop', ['shopId' => $pickupShop->id]))
        ->setLabel(sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirm_shop'][0], $pickupShop->getLabel()))
        ->setLinkAttribute('title', sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirm_shop'][1], $pickupShop->getLabel()))
        ->setLinkAttribute('class', 'isotopepackagingslipbarcodescanner_confirmhop isotopepackagingslipbarcodescanner_confirmhop_'.$pickupShop->id)
        ->setCurrent($this->requestStack->getCurrentRequest()->get('_controller') === 'isotopepackagingslipbarcodescanner_confirmshop_'.$pickupShop->id)
      ;
      $contentNode->addChild($nodePickup);

      $nodeDelivery = $factory
        ->createItem('isotopepackagingslipbarcodescanner_deliveryshop_'.$pickupShop->id)
        ->setUri($this->router->generate('isotopepackagingslipbarcodescanner_deliveryshop', ['shopId' => $pickupShop->id]))
        ->setLabel(sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['delivery_shop'][0], $pickupShop->getLabel()))
        ->setLinkAttribute('title', sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['delivery_shop'][1], $pickupShop->getLabel()))
        ->setLinkAttribute('class', 'isotopepackagingslipbarcodescanner_confirmhop isotopepackagingslipbarcodescanner_deliveryhop_'.$pickupShop->id)
        ->setCurrent($this->requestStack->getCurrentRequest()->get('_controller') === 'isotopepackagingslipbarcodescanner_deliveryshop_'.$pickupShop->id)
      ;
      $contentNode->addChild($nodeDelivery);
    }

  }

  /**
   * @return \Contao\Model|\Contao\Model[]|\Contao\Model\Collection|\Isotope\Model\Shipping|null
   */
  protected function getAvailablePickupShops() {
    return $this->container->get('krabo.isotopepackagingslipbarcodescanner.helper')->getRestrictedPickupShopShippingMethods();
  }

}