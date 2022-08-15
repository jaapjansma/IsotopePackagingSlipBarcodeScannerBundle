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

namespace Krabo\IsotopePackagingSlipBarcodeScannerBundle\Twig;

use Krabo\IsotopePackagingSlipBarcodeScannerBundle\Helper\ProductStockHelper;
use Krabo\IsotopeStockBundle\Model\AccountModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  public function getFunctions(): array {
    return [
      new TwigFunction('isotopePackagingSlipBarcodeScannerProductStock', [$this, 'isotopePackagingSlipBarcodeScannerProductStock']),
      new TwigFunction('isotopePackagingSlipBarcodeScannerAccountTitle', [$this, 'isotopePackagingSlipBarcodeScannerAccountTitle'])
    ];
  }


  public function isotopePackagingSlipBarcodeScannerProductStock(int $product_id, int $account_id, int $exclude_status, int $quantity): string
  {
    $stock = ProductStockHelper::getProductCountPerAccount($product_id, $account_id);
    $excludeStock = ProductStockHelper::getProductCountPerAccountAndPackagingSlipStatus($product_id, $account_id, $exclude_status);
    $stock = $stock + $excludeStock;
    if (($stock - $quantity) > 0) {
      return '<span class="in-stock">' . $stock . '</span>';
    }
    return '<span class="out-of-stock">' . $stock . '&nbsp;' . $GLOBALS['TL_LANG']['IsotopePackagingSlipBarcodeScannerBundle']['NotEnoughInStock'] .'</span>';
  }

  public function isotopePackagingSlipBarcodeScannerAccountTitle(int $account_id): string {
    $account = AccountModel::findByPk($account_id);
    return $account->title;
  }

}