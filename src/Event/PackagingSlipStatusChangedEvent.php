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

class PackagingSlipStatusChangedEvent {

  const EVENT_STATUS_SHIPPED = 'krabo.isotopepackagingslipbarcodescanner.status_shipped';

  const EVENT_STATUS_READY_FOR_PICKUP = 'krabo.isotopepackagingslipbarcodescanner.status_ready_for_pickup';

  const EVENT_STATUS_DELIVERED = 'krabo.isotopepackagingslipbarcodescanner.status_delivered';

  /**
   * @var \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel
   */
  protected $packagingSlip;

  /**
   * @var array
   */
  protected array $submittedData = [];

  public function __construct(IsotopePackagingSlipModel $packagingSlipModel, array $submittedData=[]) {
    $this->packagingSlip = $packagingSlipModel;
    $this->submittedData = $submittedData;
  }

  /**
   * @return \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel
   */
  public function getPackagingSlip(): IsotopePackagingSlipModel {
    return $this->packagingSlip;
  }

  /**
   * @return array
   */
  public function getSubmittedData(): array {
    return $this->submittedData;
  }

}
