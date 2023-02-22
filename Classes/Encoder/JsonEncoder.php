<?php

namespace Madj2k\AjaxApi\Encoder;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Madj2k\AjaxApi\Utilities\DomUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class JsonEncoder
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_AjaxApi
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class JsonEncoder extends AbstractJsonEncoder
{


    /**
     * Sets HTML by given list of ids in DOM
     *
     * @param string $html
     * @param array $idList
     * @param string $idPrefix
     * @return self
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setHtmlByDom(string $html, array $idList, string $idPrefix = ''): self
    {
        /** @var DomUtility $domUtility */
        $domUtility = GeneralUtility::makeInstance(DomUtility::class);

        // now get contents for all given ids
        foreach ($idList as $id) {

            $finalId = $idPrefix . '-' . $id;
            $element = $domUtility::getElementById($html, $finalId);

            if ($element instanceof \DOMElement) {
                $type = $element->getAttribute('data-tx-ajax-api-action');
                $finalType = 'replace';
                if (in_array(strtolower($type), array('append', 'prepend', 'replace'))) {
                    $finalType = strtolower($type);
                }

                $this->html[$finalId][$finalType] = $domUtility::getInnerHTML($element);
            }
        }

        return $this;
    }

}
