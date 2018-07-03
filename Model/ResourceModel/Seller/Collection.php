<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\ResourceModel\Seller;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\CollectionDataSourceInterface;

/**
 * Sellers Collection
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName) The properties are inherited
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) The parent class had already too much coupling.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends AbstractCollection implements CollectionDataSourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'smile_seller_entity_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'smile_seller_entity_collection';

    /**
     * @var null Attribute set id of the entity
     */
    protected $sellerAttributeSetId = null;

    /**
     * @var null Attribute set name of the entity
     */
    protected $sellerAttributeSetName = null;

    /**
     * Current scope (store Id)
     *
     * @var integer
     */
    private $storeId;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) Parent construct already has 10 arguments.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory             $entityFactory    Entity Factory
     * @param \Psr\Log\LoggerInterface                                     $logger           Logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy    Fetch Strategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager     Event Manager
     * @param \Magento\Eav\Model\Config                                    $eavConfig        EAV Config
     * @param \Magento\Framework\App\ResourceConnection                    $resource         Resource Connection
     * @param \Magento\Eav\Model\EntityFactory                             $eavEntityFactory EAV Entity Factory
     * @param \Magento\Eav\Model\ResourceModel\Helper                      $resourceHelper   Resource Helper
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager     The Store Manager
     * @param \Magento\Framework\Validator\UniversalFactory                $universalFactory Universal Factory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection       Database Connection
     * @param string|NULL                                                  $attributeSetName Seller Attribute Set Name
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $attributeSetName = null
    ) {
        $this->sellerAttributeSetName = $attributeSetName;
        $this->storeManager           = $storeManager;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Model\Store $store The store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->setStoreId($this->storeManager->getStore($store)->getId());

        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Model\Store $storeId The store Id or Store
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->storeId = (int) $storeId;

        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * Init collection and determine table names
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smile\Seller\Model\Seller', 'Smile\Seller\Model\ResourceModel\Seller');

        if ($this->sellerAttributeSetId == null) {
            if ($this->sellerAttributeSetName !== null) {
                $this->sellerAttributeSetId = $this->getResource()->getAttributeSetIdByName($this->sellerAttributeSetName);
            }
        }
    }

    /**
     * Init select. Retrieve only sellers of current attribute set if specified.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->sellerAttributeSetId !== null) {
            $this->addFieldToFilter('attribute_set_id', (int) $this->sellerAttributeSetId);
        }

        return $this;
    }

    /**
     * Retrieve attributes load select
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @param string    $table        The table to load attributes from
     * @param array|int $attributeIds The attribute ids to load
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        $storeId    = $this->getStoreId();
        $connection = $this->getConnection();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        $select = $this->getBaseAttributesSelect($table, $attributeIds);

        $storeCondition = $this->getDefaultStoreId();

        if ($storeId) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$entityIdField} = t_d.{$entityIdField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select->joinLeft(['t_s' => $table], implode(' AND ', $joinCondition), []);

            $storeCondition = $connection->getIfNullSql('t_s.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        }

        $select->where('t_d.store_id = ?', $storeCondition);

        return $select;
    }

    /**
     * Adding join statement to collection select instance
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) The method is inherited
     * @SuppressWarnings(PHPMD.ElseExpression) The method is inspired from \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     *
     * @param string $method     The join method
     * @param object $attribute  The attribute to join
     * @param string $tableAlias The table alias
     * @param array  $condition  The condition
     * @param string $fieldCode  The field code
     * @param string $fieldAlias The field alias
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        $storeId = $this->getStoreId();
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $storeId = $this->_joinAttributes[$fieldCode]['store_id'];
        }

        $connection = $this->getConnection();

        $scopeGlobal = method_exists($attribute, 'isScopeGlobal') ? $attribute->isScopeGlobal() : false;
        if ($storeId != $this->getDefaultStoreId() && !$scopeGlobal) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '(' . implode(') AND (', $condition) . ')';
            $defAlias = $tableAlias . '_default';
            $defAlias = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias = str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias = $this->getConnection()->getTableName($tableAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition .= $connection->quoteInto(
                " AND " . $connection->quoteColumnAs("{$defAlias}.store_id", null) . " = ?",
                $this->getDefaultStoreId()
            );

            $this->getSelect()->{$method}(
                [$defAlias => $attribute->getBackend()->getTable()],
                $defCondition,
                []
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql(
                "{$tableAlias}.value_id > 0",
                $fieldAlias,
                $defFieldAlias
            );
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute'] = $attribute;
        } else {
            $storeId = $this->getDefaultStoreId();
        }

        $condition[] = $connection->quoteInto(
            $connection->quoteColumnAs("{$tableAlias}.store_id", null) . ' = ?',
            $storeId
        );

        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }

    /**
     * Retrieve Base select for attributes of this collection.
     *
     * @param string $table        The attribute table
     * @param array  $attributeIds The attribute ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Framework\Db\Select
     */
    private function getBaseAttributesSelect($table, $attributeIds = [])
    {
        $connection    = $this->getConnection();
        $entityTable   = $this->getEntity()->getEntityTable();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        $select = $connection->select()->from(
            ['t_d' => $table],
            ['attribute_id']
        )->join(
            ['e' => $entityTable],
            "e.{$entityIdField} = t_d.{$entityIdField}",
            ['e.entity_id']
        )->where(
            "e.entity_id IN (?)",
            array_keys($this->_itemsById)
        )->where(
            't_d.attribute_id IN (?)',
            $attributeIds
        );

        return $select;
    }
}
