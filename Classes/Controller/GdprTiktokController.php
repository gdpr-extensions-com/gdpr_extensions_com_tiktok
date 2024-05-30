<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComTiktok\Controller;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "gdpr-extensions-com-tiktok" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023
 */

/**
 * gdprtiktokController
 */
class GdprTiktokController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * gdprManagerRepository
     *
     * @var \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository
     */

    protected $gdprManagerRepository = null;

    /**
     * ContentObject
     *
     * @var ContentObject
     */
    protected $contentObject = null;

    /**
     * Action initialize
     */
    protected function initializeAction()
    {
        $this->contentObject = $this->configurationManager->getContentObject();

        // intialize the content object
    }

    /**
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository $gdprManagerRepository
     */
    public function injectGdprManagerRepository(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository $gdprManagerRepository)
    {
        $this->gdprManagerRepository = $gdprManagerRepository;
    }

    /**
     * action index
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function indexAction(): \Psr\Http\Message\ResponseInterface
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomyoutube_domain_model_gdprmanager');
        $gdprSettingTiktok = $queryBuilder
            ->select('*')
            ->from('tx_gdprextensionscomyoutube_domain_model_gdprmanager')->where(
                $queryBuilder->expr()->like('extension_title', $queryBuilder->createNamedParameter('%' . (string)'tiktok' . '%'))
            );
        $settings =  $gdprSettingTiktok->execute()->fetchAssociative();

        $tikTokUrl = $this->contentObject->data['gdpr_tiktok_url'];
        $videoId = '';

        // Regular expression for TikTok URL format
        if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/', $tikTokUrl, $matches)) {
            $videoId = $matches[1];
        }

        // Construct the TikTok embed URL
        $embedUrl = $videoId ? "https://www.tiktok.com/embed/v2/$videoId" : '';

        if (!empty($embedUrl)) {
            $this->contentObject->data['gdpr_tiktok_url'] = $embedUrl;
        }
        $this->view->assign('TiktokData', $this->contentObject->data);
        $this->view->assign('rootPid', $GLOBALS['TSFE']->site->getRootPageId());
        $this->view->assign('TiktokSettings', $settings);
        return $this->htmlResponse();
    }
}
