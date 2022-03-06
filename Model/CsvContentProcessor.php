<?php

declare(strict_types=1);

namespace SkyOptical\ExportCmsData\Model;

use Magento\PageBuilder\Model\Filter\Template;
use Magento\PageBuilder\Model\Config;

class CsvContentProcessor
{
    /**
     * @var Template
     */
    private $pagebuilderTemplate;

    /**
     * @var Config
     */
    private $pagebuilderConfig;

    /**
     * @param Template $pagebuilderTemplate
     * @param Config $pagebuilderConfig
     */
    public function __construct(
        Template $pagebuilderTemplate,
        Config   $pagebuilderConfig
    ) {
        $this->pagebuilderTemplate = $pagebuilderTemplate;
        $this->pagebuilderConfig = $pagebuilderConfig;
    }

    /**
     * @param $blocks
     * @return string[]
     */
    public function getColumnHeaders($cmsData): array
    {
        return array_keys($cmsData->getFirstItem()->getData());
    }

    /**
     * @param $pageData
     * @return array
     */
    public function convertArrayToString($cmsData): array
    {
        $sanitizedCmsData = [];
        foreach ($cmsData as $columnIdentifier => $cmsColumnData) {

            if (is_array($cmsColumnData)) {
                $cmsColumnData = implode(",", $cmsColumnData);
            }

            $sanitizedCmsData[$columnIdentifier] = $cmsColumnData;
        }
        return $sanitizedCmsData;
    }

    /**
     * @param $pageData
     * @return string
     */
    public function isCmsDataPageBuilder($cmsData)
    {
        if ($this->pagebuilderConfig->isEnabled()) {
            $cmsTypeContent = $this->pagebuilderTemplate->filter($cmsData['content']);
        } else {
            $cmsTypeContent = $cmsData['content'];
        }
        return $cmsTypeContent;
    }

    /**
     * @param $cmsContentSet
     * @return array
     */
    public function prepareDataForCsvDownload($cmsContentSet)
    {
        $cmsContentDataSet = [];
        $cmsContentDataSet[] = $this->getColumnHeaders($cmsContentSet);
        foreach ($cmsContentSet as $singleCmsContent) {

            $singleCmsContent['content'] = $this->isCmsDataPageBuilder($singleCmsContent);

            $singleCmsContent = $this->convertArrayToString($singleCmsContent->getData());
            $cmsContentDataSet[] = $singleCmsContent;
        }
        return $cmsContentDataSet;
    }
}

