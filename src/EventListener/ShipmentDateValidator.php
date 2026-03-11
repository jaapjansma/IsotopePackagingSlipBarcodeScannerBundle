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
use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\FormBuilderWithPackagingSlipEvent;
use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\FormValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ShipmentDateValidator implements EventSubscriberInterface {

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
      FormBuilderEvent::EVENT_NAME => 'onBuildForm',
      FormValidationEvent::EVENT_NAME => 'onFormValidation',
      FormBuilderWithPackagingSlipEvent::EVENT_NAME => 'onBuildConfirmForm',
    ];
  }

  public function onBuildForm(FormBuilderEvent $event) {
    $event->formBuilder->add('confirm_shipping_date', HiddenType::class);
    $event->formBuilder->get('confirm_shipping_date')->setData('0');
    //$event->additionalWidgets[] = 'confirm_shipping_date';
  }

  public function onFormValidation(FormValidationEvent $event) {
    if (!$event->isValid || $event->shopId != '__store') {
      return;
    }

    $submittedData = $event->form->getData();
    if (!isset($submittedData['confirm_document_number'])) {
      return;
    }

    /** @var \DateTime $shippingDate */
    $submittedShippingDate = $submittedData['shipping_date'];
    $submittedShippingDate->setTime(0,0,0,0);
    $expectedShippingDate = \DateTime::createFromFormat('U', $event->packagingSlip->scheduled_shipping_date);
    $expectedShippingDate->setTime(0,0,0);
    if ($expectedShippingDate != $submittedShippingDate && (empty($submittedData['confirm_shipping_date']) || $submittedData['confirm_shipping_date'] != $submittedShippingDate->getTimestamp())) {
      $event->errorMessage = sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['PackageSlipWrongShippingDate']);
      $event->isValid = false;
      $event->stayOnCurrentScreen = true;
    } 
  }

  public function onBuildConfirmForm(FormBuilderWithPackagingSlipEvent $event) {
    if ($event->shopId != '__store') {
      return;
    }

    if (!isset($event->submittedData['shipping_date'])) {
      return;
    }

    $submittedData = $event->submittedData;
    /** @var \DateTime $shippingDate */
    $submittedShippingDate = $submittedData['shipping_date'];
    $submittedShippingDate->setTime(0,0,0,0);
    $expectedShippingDate = \DateTime::createFromFormat('U', $event->packagingSlip->scheduled_shipping_date);
    $expectedShippingDate->setTime(0,0,0);
    if ($expectedShippingDate != $submittedShippingDate) {
      $event->formBuilder->get('confirm_shipping_date')->setData($submittedShippingDate->getTimestamp());
    } 
  }

}