<?php

declare(strict_types=1);

namespace SkyOptical\ExportCmsData\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

class CsvDataExporter
{
    /**
     * @var BlockCollectionFactory
     */
    protected $blockCollectionFactory;

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var CsvContentProcessor
     */
    private $csvContentProcessor;

    public function __construct(
        Filesystem             $filesystem,
        BlockCollectionFactory $blockCollectionFactory,
        PageCollectionFactory  $pageCollectionFactory,
        FileFactory            $fileFactory,
        TimezoneInterface      $timezone,
        DirectoryList          $directoryList,
        Csv                    $csvProcessor,
        CsvContentProcessor    $csvContentProcessor
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->csv = $csvProcessor;
        $this->timezone = $timezone;
        $this->filesystem = $filesystem;
        $this->csvContentProcessor = $csvContentProcessor;
    }

    /**
     * @return ResponseInterface
     * @throws FileSystemException
     */
    public function cmsExport($selectedItems, $type): ResponseInterface
    {
        return $this->downloadCsv($selectedItems, $type);
    }

    /**
     * @return ResponseInterface
     * @throws FileSystemException
     * @throws \Exception
     */
    public function downloadCsv($cmsContentSet, $type): ResponseInterface
    {
        $csvFilename = 'Cms' . $type . '.csv';
        $fullCsvFileName = 'export/custom_' . $this->timezone->date()->format('m_d_Y_H_i_s') . $csvFilename;
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fullCsvFileName;

        $cmsContentForDownload = $this->csvContentProcessor->prepareDataForCsvDownload($cmsContentSet);

        $this->csv
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $cmsContentForDownload
            );

        return $this->fileFactory->create(
            $fullCsvFileName,
            [
                'type' => "filename",
                'value' => $fullCsvFileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'text/csv',
            null
        );
    }

    /**
     * @param $type
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    public function getCollectionFactoryByType($type)
    {
        if ($type === 'block') {
            return $this->blockCollectionFactory->create();
        }
        return $this->pageCollectionFactory->create();
    }
}
