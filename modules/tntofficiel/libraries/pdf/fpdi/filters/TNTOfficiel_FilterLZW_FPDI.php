<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

//
//  FPDI - Version 1.3.1
//
//    Copyright 2004-2009 Setasign - Jan Slabon
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

require_once 'TNTOfficiel_FilterLZW.php';

class TNTOfficiel_FilterLZW_FPDI extends TNTOfficiel_FilterLZW {

    var $fpdi;

    function TNTOfficiel_FilterLZW_FPDI(&$fpdi) {
        $this->fpdi =& $fpdi;
    }

    function error($msg) {
        $this->fpdi->error($msg);
    }
}