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

namespace Krabo\IsotopePackagingSlipBarcodeScannerBundle\Event;

use Symfony\Component\Form\FormInterface;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;

class FormValidationEvent {

  const EVENT_NAME = 'krabo.isotopepackagingslipbarcodescanner.form_validation';

  /**
   * @var bool
   */
  public bool $stayOnCurrentScreen = false;

  /**
   * @var bool
   */
  public bool $isValid = true;

  /**
   * @var string
   */
  public string $errorMessage = '';

  /**
   * @var string
   */
  public string $shopId;

  /**
   * @var FormInterface
   */
  public FormInterface $form;

    /**
   * @var \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel
   */
  public $packagingSlip;

  public function __construct(FormInterface $form, string $shopId, IsotopePackagingSlipModel $packagingSlip) {
    $this->form = $form;
    $this->shopId = $shopId;
    $this->packagingSlip = $packagingSlip;
  }

}