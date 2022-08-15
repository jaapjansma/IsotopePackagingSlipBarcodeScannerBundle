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

use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderWithPackagingSlipEvent {

  const EVENT_NAME = 'krabo.isotopepackagingslipbarcodescanner.form_builder_with_packaging_slip';

  /**
   * @var FormBuilderInterface
   */
  public $formBuilder;

  /**
   * @var string
   */
  public string $shopId;

  /**
   * @var \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel
   */
  public $packagingSlip;

  public function __construct(FormBuilderInterface $formBuilder, string $shopId, IsotopePackagingSlipModel $packagingSlip) {
    $this->formBuilder = $formBuilder;
    $this->shopId = $shopId;
    $this->packagingSlip = $packagingSlip;
  }

}