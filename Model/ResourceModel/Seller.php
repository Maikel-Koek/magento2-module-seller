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

namespace Smile\Seller\Model\ResourceModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

/**
 * Seller Resource Model
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Seller extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * Id of 'is_active' seller attribute
     *
     * @var int
     */
    protected $isActiveAttributeId = null;

    /**
     * Store id
     *
     * @var int
     */
    protected $storeId = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager = null;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * Seller constructor.
     *
     * @param \Magento\Eav\Model\Entity\Context              $context       Entity Context
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManager  Store Manager
     * @param \Magento\Framework\Event\ManagerInterface      $eventManager  Event Manager
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager Entity Manager
     * @param array                                          $data          Seller data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager  = $storeManager;
        $this->eventManager  = $eventManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Entity type getter and lazy loader
     *
     * @return \Magento\Eav\Model\Entity\Type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType(\Smile\Seller\Model\Seller::ENTITY);
        }
        return parent::getEntityType();
    }

    /**
     * Set store Id
     *
     * @param integer $storeId The store Id
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            return $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * Check if seller id exist
     *
     * @param int $entityId The Seller Id
     *
     * @return bool
     */
    public function checkId($entityId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => $entityId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Check array of seller identifiers
     *
     * @param array $ids The seller ids
     *
     * @return array
     */
    public function verifyIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    public function getIsActiveAttributeId()
    {
        if ($this->isActiveAttributeId === null) {
            $this->isActiveAttributeId = (int) $this->_eavConfig
                ->getAttribute($this->getEntityType(), 'is_active')
                ->getAttributeId();
        }

        return $this->isActiveAttributeId;
    }


    /**
     * Reset firstly loaded attributes
     *
     * @param \Magento\Framework\DataObject $object     Object being loaded
     * @param integer                       $entityId   The entity Id
     * @param array|null                    $attributes The attributes
     *
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $this->_attributes = [];
        $this->loadAttributesMetadata($attributes);
        $object = $this->getEntityManager()->load($object, $entityId);
        if (!$this->getEntityManager()->has($object)) {
            $object->isObjectNew(true);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->getEntityManager()->delete($object);
        $this->eventManager->dispatch(
            'catalog_category_delete_after_done',
            ['product' => $object]
        );
        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param \Magento\Framework\Model\AbstractModel $object The Object
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getEntityManager()->save($object);

        return $this;
    }
}