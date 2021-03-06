<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Api\Data;

/**
 * Seller Attributes Interface
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
interface SellerAttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    const ENTITY_TYPE_CODE = 'smile_seller';

    /**
     * Check if attribute has a global scope
     *
     * @return bool
     */
    public function isScopeGlobal();

    /**
     * Check if attribute has a website scope
     *
     * @return bool
     */
    public function isScopeWebsite();

    /**
     * Retrieve attribute has a store scope
     *
     * @return bool
     */
    public function isScopeStore();
}
