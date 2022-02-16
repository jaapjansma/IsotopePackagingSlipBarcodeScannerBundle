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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_user']['fields']['restrict_pickup_shop'] = [
  'inputType' => 'checkbox',
  'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
  'sql' => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => '']
];

$GLOBALS['TL_DCA']['tl_user']['fields']['restricted_pickup_shops'] = [
  'inputType'               => 'checkbox',
  'eval'                    => array('tl_class' => 'w50', 'multiple' => true),
  'sql'                     => "varchar(255) NOT NULL default ''",
  'options_callback'        => function(\Contao\DataContainer $dc=null) {
    $return = [];
    $shippingMethods = \Contao\System::getContainer()->get('krabo.isotopepackagingslipbarcodescanner.helper')->getPickupShopShippingMethods();
    foreach($shippingMethods as $shippingMethod) {
      $return[$shippingMethod->id] = $shippingMethod->label;
    }
    return $return;
  },
  'default'                 => '',
];

$GLOBALS['TL_DCA']['tl_user']['palettes']['__selector__'][] = 'restrict_pickup_shop';
$GLOBALS['TL_DCA']['tl_user']['subpalettes']['restrict_pickup_shop'] = 'restricted_pickup_shops';

foreach($GLOBALS['TL_DCA']['tl_user']['palettes'] as $palette_name => $palette) {
  if ($palette_name == '__selector__') {
    continue;
  }

  PaletteManipulator::create()
    ->addLegend('isotopepackagingslipbarcodescanner_legend', 'admin_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('restrict_pickup_shop', 'isotopepackagingslipbarcodescanner_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette($palette_name, 'tl_user');
}