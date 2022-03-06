<?php

declare(strict_types=1);

namespace SkyOptical\ExportCmsData\Controller\Adminhtml\CsvExport;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use SkyOptical\ExportCmsData\Model\CsvDataExporter;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Export
 *
 * @package SkyOptical\ExportCmsData\Controller\Adminhtml\CsvExport
 */
class Export extends Action implements HttpPostActionInterface
{
    /**
     * @var CsvDataExporter
     */
    private $csvDataExporter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CsvDataExporter $csvDataExporter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        Filter                $filter,
        CsvDataExporter       $csvDataExporter,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->csvDataExporter = $csvDataExporter;
        $this->filter = $filter;
        $this->context = $context;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        try {
            $collection = $this->filter->getCollection($this->csvDataExporter->getCollectionFactoryByType($type));
            return $this->csvDataExporter->cmsExport($collection, $type);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('cms/' . $type . '/index');
        return $resultRedirect;
    }
}
