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

use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\FormBuilderEvent;
use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\FormValidationEvent;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipShipperModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;

class StoreShipperListener implements EventSubscriberInterface {

  private RequestStack $requestStack;

  public function __construct(RequestStack $requestStack)
  {
    $this->requestStack = $requestStack;
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * ['eventName' => 'methodName']
   *  * ['eventName' => ['methodName', $priority]]
   *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
   *
   * The code must not depend on runtime state as it will only be called at compile time.
   * All logic depending on runtime state must be put into the individual methods handling the events.
   *
   * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
   */
  public static function getSubscribedEvents() {
    return [
      FormBuilderEvent::EVENT_NAME => 'onFormBuild',
      FormValidationEvent::EVENT_NAME => 'onFormValidation',
    ];
  }

  public function onFormBuild(FormBuilderEvent $event) {
    if ($event->shopId != '__store') {
      return;
    }
    $objShippers = IsotopePackagingSlipShipperModel::findBy('store_handling', '1');
    $shippers = [];
    $selectedShipper = null;
    while($objShippers->next()) {
      if ($selectedShipper === null) {
        $selectedShipper = $objShippers->id;
      }
      $shippers[$objShippers->name] = $objShippers->id;
    }
    $event->formBuilder->add('shipper', ChoiceType::class, [
      'label' => $GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['Shipper'],
      'choices' => $shippers,
      'attr' => [
        'class' => 'tl_radio',
      ],
      'row_attr' => [
        'class' => 'tl_radio_container'
      ],
      'expanded' => true,
      'multiple' => false,
    ]);

    if ($this->requestStack->getCurrentRequest()->getSession()->has('krabo.isotope-packaging-slip-barcode-scanner.shipper')) {
      $selectedShipper = $this->requestStack->getCurrentRequest()->getSession()->get('krabo.isotope-packaging-slip-barcode-scanner.shipper');
    }
    $event->formBuilder->get('shipper')->setData($selectedShipper);

    $event->additionalWidgetsFirstScreen[] = 'shipper';
  }

  public function onFormValidation(FormValidationEvent $event) {
    if (!$event->isValid || $event->shopId != '__store') {
      return;
    }

    $submittedData = $event->form->getData();
    if (isset($submittedData['shipper'])) {
      $this->requestStack->getCurrentRequest()->getSession()->set('krabo.isotope-packaging-slip-barcode-scanner.shipper', $submittedData['shipper']);
    }
    if (!empty($submittedData['shipper']) && $submittedData['shipper'] != $event->packagingSlip->shipper_id) {
      $shipper = IsotopePackagingSlipShipperModel::findByPk($event->packagingSlip->shipper_id);
      $event->errorMessage = sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['PackageSlipWrongShipper'], $shipper->name);
      $event->isValid = false;
    } 
  }

}