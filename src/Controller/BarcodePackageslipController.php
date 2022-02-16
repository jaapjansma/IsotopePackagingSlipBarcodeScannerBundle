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

namespace Krabo\IsotopePackagingSlipBarcodeScannerBundle\Controller;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\Message;
use Contao\System;
use Isotope\Model\Shipping;
use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event\PackagingSlipStatusChangedEvent;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

/**
 * @Route("/contao/isotopepackagingslipbarcodescanner", name="isotopepackagingslipbarcodescanner")
 */
class BarcodePackageslipController extends AbstractController {

  /**
   * @var \Twig\Environment
   */
  private $twig;

  /**
   * @var ContaoCsrfTokenManager
   */
  private $tokenManager;

  /**
   * @var string
   */
  private $csrfTokenName;

  public function __construct(TwigEnvironment $twig, ContaoCsrfTokenManager $tokenManager)
  {
    $this->twig = $twig;
    $this->tokenManager = $tokenManager;
    $this->csrfTokenName = System::getContainer()->getParameter('contao.csrf_token_name');
  }

  /**
   * @Route("/confirmstore",
   *     name="_confirmstore",
   *     defaults={"_scope": "backend", "_token_check": true}
   * )
   */
  public function confirmStoreAction(Request $request) {
    $redirectUrl = $this->generateUrl('isotopepackagingslipbarcodescanner_confirmstore');
    return $this->generate($request, IsotopePackagingSlipModel::STATUS_PREPARE_FOR_SHIPPING, IsotopePackagingSlipModel::STATUS_SHIPPED, $redirectUrl,PackagingSlipStatusChangedEvent::EVENT_STATUS_SHIPPED);
  }

  /**
   * @Route("/deliveryshop/{shopId}",
   *     name="_deliveryshop",
   *     defaults={"_scope": "backend", "_token_check": true}
   * )
   */
  public function deliveryShopAction(int $shopId, Request $request) {
    $redirectUrl = $this->generateUrl('isotopepackagingslipbarcodescanner_deliveryshop', ['shopId' => $shopId]);
    return $this->generate($request, IsotopePackagingSlipModel::STATUS_PREPARE_FOR_SHIPPING, IsotopePackagingSlipModel::STATUS_READY_FOR_PICKUP, $redirectUrl, PackagingSlipStatusChangedEvent::EVENT_STATUS_READY_FOR_PICKUP, $shopId);
  }

  /**
   * @Route("/confirmshop/{shopId}",
   *     name="_confirmshop",
   *     defaults={"_scope": "backend", "_token_check": true}
   * )
   */
  public function confirmShopAction(int $shopId, Request $request) {
    $redirectUrl = $this->generateUrl('isotopepackagingslipbarcodescanner_confirmshop', ['shopId' => $shopId]);
    return $this->generate($request, IsotopePackagingSlipModel::STATUS_READY_FOR_PICKUP, IsotopePackagingSlipModel::STATUS_DELIVERED, $redirectUrl, PackagingSlipStatusChangedEvent::EVENT_STATUS_DELIVERED, $shopId);
  }

  protected function generate(Request $request, $currentStatus, $newStatus, $redirectUrl, $eventName, $shopId=null) {
    if ($shopId) {
      $this->checkAccessToShop($shopId);
    }
    $GLOBALS['TL_JAVASCRIPT'][] = 'assets/jquery/js/jquery.min.js|static';
    $packagingSlipTable = IsotopePackagingSlipModel::getTable();
    System::loadLanguageFile('default');
    System::loadLanguageFile($packagingSlipTable);
    $viewData = [
      'title' => $GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirm_store'][0],
      'lang' => $GLOBALS['TL_LANG'][$packagingSlipTable],
      'lang_msc' => $GLOBALS['TL_LANG']['MSC'],
      'packagingSlip' => null,
      'messages' => Message::generateUnwrapped('isotopepackagingslipbarcodescanner_confirmstore', false),
    ];
    $defaultData = [];
    $formBuilder = $this->createFormBuilder($defaultData);
    $formBuilder->add('document_number', TextType::class, [
      'label' => $GLOBALS['TL_LANG'][$packagingSlipTable]['document_number'][0],
      'attr' => [
        'class' => 'tl_text',
      ],
      'row_attr' => [
        'class' => 'widget'
      ],
    ]);
    $formBuilder->add('save', SubmitType::class, [
      'label' => $GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['confirmBarcodeStore'],
      'attr' => [
        'class' => 'tl_submit',
      ]
    ]);
    $formBuilder->add('REQUEST_TOKEN', HiddenType::class, [
      'data' => $this->tokenManager->getToken($this->csrfTokenName)
    ]);
    $formBuilder->add('confirm_document_number', HiddenType::class);

    $form = $formBuilder->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $submittedData = $form->getData();
      $packagingSlip = IsotopePackagingSlipModel::findOneBy('document_number', $submittedData['document_number']);
      if (!$packagingSlip || $packagingSlip->status != $currentStatus) {
        $msg = sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['PackageSlipNotFound'], $submittedData['document_number']);
        Message::addError($msg, 'isotopepackagingslipbarcodescanner_confirmstore');
        return $this->redirect($redirectUrl);
      } elseif ($shopId && $packagingSlip->shipping_id != $shopId) {
        $msg = sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['PackageSlipNotFound'], $submittedData['document_number']);
        Message::addError($msg, 'isotopepackagingslipbarcodescanner_confirmstore');
        return $this->redirect($redirectUrl);
      }
      if (isset($submittedData['confirm_document_number']) && $submittedData['confirm_document_number'] == $submittedData['document_number']) {
        $packagingSlip->status = $newStatus;
        $packagingSlip->save();
        $this->triggerStatusEvent($packagingSlip, $eventName);
        $newStatusLabel = $GLOBALS['TL_LANG'][$packagingSlipTable]['status_options'][$newStatus];
        $msg = sprintf($GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['StatusUpdated'], $newStatusLabel);
        Message::addInfo($msg, 'isotopepackagingslipbarcodescanner_confirmstore');

        return $this->redirect($redirectUrl);
      }
      $shippingMethod = Shipping::findByPk($packagingSlip->shipping_id);
      $viewData['packagingSlip'] = $packagingSlip;
      $viewData['shippingMethod'] = $shippingMethod;
      $viewData['orders'] = $packagingSlip->getOrders();
      $formBuilder->get('confirm_document_number')->setData($submittedData['document_number']);
      $form = $formBuilder->getForm();
    }

    $viewData['form'] = $form->createView();
    return new Response($this->twig->render('@IsotopePackagingSlipBarcodeScanner/confirmstore.html.twig', $viewData));
  }

  /**
   * @return \Contao\Model|\Contao\Model[]|\Contao\Model\Collection|\Isotope\Model\Shipping|null
   */
  protected function checkAccessToShop($shopId): void {
    $availableShopIds = System::getContainer()->get('krabo.isotopepackagingslipbarcodescanner.helper')->getRestrictedPickupShopShippingMethods();
    if (!isset($availableShopIds[$shopId])) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * @param \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel $packagingSlipModel
   * @param $eventName
   *
   * @return void
   */
  protected function triggerStatusEvent(IsotopePackagingSlipModel $packagingSlipModel, $eventName) {
    $event = new PackagingSlipStatusChangedEvent($packagingSlipModel);
    System::getContainer()->get('event_dispatcher')->dispatch($event, $eventName);
  }

}