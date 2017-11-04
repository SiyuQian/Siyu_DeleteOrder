<?php
namespace Siyu\DeleteOrder\Controller\Adminhtml\Order;

use Magento\Sales\Model\Order;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class MassDelete extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    protected $_quoteFactory;

     /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        QuoteFactory $quoteFactory
    )
    {
        parent::__construct($context, $filter);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_quoteFactory = $quoteFactory;
    }

    protected function massAction(AbstractCollection $collection)
    {
        foreach ($collection->getItems() as $order) {
            // Do not delete order that has not been canceled
            // Added this logic to avoid miss delete
            if ($order->getStatus() == Order::STATE_CANCELED && $order->getIncrementId()) {
                // If Order can be deleted
                // The $order->delete() only delete the data from the tables that mapped by the Order model
                // But since Magento is converting quote to order when the order has been created
                // So we would have to delete the quote data as well for a full cleanup
                $order->delete();
                $quoteId = $order->getQuoteId();
                // if quote id is existed, then we need to clean the contents in the quote tables
                if ($quoteId) {
                    $quote = $this->_quoteFactory->create()->load($quoteId);
                    $quoteCollection = $quote->getItemsCollection();
                    foreach ($quoteCollection as $item) {
                        $item->delete();
                    }
                    $quote->delete();
                }
                $this->messageManager->addSuccess(__('Order: #%1 has been deleted successfully.', $order->getIncrementId()));
            } else {
                // Add errors to the redirect page
                // for displaying the reason why the delete order action has been failed
                $this->messageManager->addError(
                    __('Cannot delete order with status: %1 #%2. The Orders only with status: \'%3\' can be deleted',
                        $order->getStatus(),
                        $order->getIncrementId(),
                        Order::STATE_CANCELED
                    )
                );
            }
        }
        // set redirect path for the page should be displayed after action performed
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/');
        return $resultRedirect;
    }
}