<?php
namespace AHT\ProductOrderGrid\Ui\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

class Order extends SearchResult
{
    protected $_idFieldName = 'entity_id';
    /**
     * @var _coreSession
     */
    protected $_coreSession;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class,
        $identifierName = null,
        $connectionName = null,
        \Magento\Framework\Session\SessionManagerInterface  $coreSession
    ) {
        $this->_coreSession = $coreSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    /**
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Request\Http');
        $request = $request->getServer('HTTP_REFERER');
        $request =explode('/',$request);
        $id = $request[9];

        parent::_initSelect();

        $this->addFilterToMap('created_at','main_table.created_at');

        // Join the 2nd Table
        $collection = $this->getSelect()->join(
            ['tablejoin' => $this->getConnection()->getTableName('sales_order_item')],
            'main_table.entity_id = tablejoin.order_id',
            array('*'))
                ->where("main_table.state != 'Complete'")
                ->where("main_table.state != 'Close'")
                // ->where("main_table.user_id = 0")
                ->where("tablejoin.product_id = ".$id);
    }
}
