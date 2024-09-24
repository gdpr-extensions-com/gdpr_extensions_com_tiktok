<?php

declare(strict_types=1);

namespace GdprExtensionsCom\GdprExtensionsComTiktok\Controller;


use GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\MapLocation;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
 * GdprManagerController
 */
class GdprManagerController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * gdprManagerRepository
     *
     * @var \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository
     */

    /**
     * @var ModuleTemplateFactory
     */
    protected $moduleTemplateFactory;
    protected $gdprManagerRepository = null;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * action index
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function indexAction(): \Psr\Http\Message\ResponseInterface
    {
        return $this->htmlResponse();

    }

    /**
     * action list
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $extensions = ExtensionManagementUtility::getLoadedExtensionListArray();
        $extensionNames = [];

        foreach ($extensions as $extensionKey) {
            if ($packageManager->isPackageAvailable($extensionKey)) {
                $extensionName = $packageManager->getPackage($extensionKey)->getPackageMetaData()->getTitle();
                // Directly assign the name to the key in the associative array.
                $extensionNames[$extensionKey] = $extensionName;
            }
        }

        // Filter based on keys, looking for 'gdpr_two_x' in the extensionKey.
        $twoClickSolutions = array_filter($extensionNames, function ($key) {
            return str_contains($key, 'gdpr_two_x') || str_contains($key, 'gdpr_extensions_com');
        }, ARRAY_FILTER_USE_KEY);


        $gdprDellQb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomyoutube_domain_model_gdprmanager');

        $gdprDellQb
            ->delete('tx_gdprextensionscomyoutube_domain_model_gdprmanager')
            ->where(
                $gdprDellQb->expr()->notIn(
                    'extension_title',
                    $gdprDellQb->createNamedParameter($twoClickSolutions,Connection::PARAM_STR_ARRAY)
                )
            )
            ->executeStatement();

        $gdprManagers = $this->gdprManagerRepository->findAll();

        $installedTwoClickSol = [];
        foreach ($gdprManagers as $twoClickSol){
            array_push($installedTwoClickSol,$twoClickSol->getExtensionTitle());
        }

        $missingExtensions = array_diff($twoClickSolutions, $installedTwoClickSol);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomyoutube_domain_model_gdprmanager');

        foreach ($missingExtensions as $key => $value) {
            $queryBuilder
                ->insert('tx_gdprextensionscomyoutube_domain_model_gdprmanager')
                ->values([
                    'extension_title' => $value,
                    'extension_key' => $key,
                    'heading' => '', // Default empty string
                    'content' => '', // Default empty string
                    'button_text' => '', // Default empty string
                    'enable_background_image' => 0, // Default 0
                    'heading_color' => '', // Default empty string
                    'background_image' => '', // Default empty string
                    'background_image_color' => '', // Default empty string
                    'button_color' => '', // Default empty string
                    'text_color' => '', // Default empty string
                    'button_shape' => '' // Default empty string
                ])
                ->execute();
        }

        $uploadImageUrl = $this->uriBuilder->reset()
            ->uriFor('uploadImage');
        $saveCookieWidget = $this->uriBuilder->reset()
            ->uriFor('cookieWidget');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomyoutube_domain_model_gdprmanager');

        $queryBuilder
            ->select('*')
            ->from('tx_gdprextensionscomyoutube_domain_model_cookiewidget');

        $result = $queryBuilder->execute()->fetchAssociative();

        $tiktokData = $this->gdprManagerRepository->findByExtension_key('gdpr_extensions_com_tiktok')->toArray();
        return $this->redirect('edit',null,null,['gdprManager' => $tiktokData[0]]);
        $this->view->assign('uploadImageUrl', $uploadImageUrl);
        $this->view->assign('cookieWidgetData', $result);
        $gdprManagers = $this->gdprManagerRepository->findAll();
        $this->view->assign('gdprManagers', $gdprManagers);
        return $this->htmlResponse();
    }

    /**
     * action show
     *
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showAction(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assign('gdprManager', $gdprManager);
        return $this->htmlResponse();
    }

    /**
     * action new
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function newAction(): \Psr\Http\Message\ResponseInterface
    {
        $uploadImageUrl = $this->uriBuilder->reset()
            ->uriFor('uploadImage');
        $this->view->assign('uploadImageUrl', $uploadImageUrl);

        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $newGdprManager
     */
    public function createAction(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $newGdprManager)
    {
        $this->gdprManagerRepository->add($newGdprManager);
        $this->redirect('list');
    }

    /**
     * action edit
     *
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("gdprManager")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function editAction(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager): \Psr\Http\Message\ResponseInterface
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        $configurations = [];

        foreach ($sites as $siteKey => $site) {
            $configurations[$siteKey] = $site->getConfiguration();
        }

        $uploadImageUrl = $this->uriBuilder->reset()
            ->uriFor('uploadImage');
        $this->view->assign('uploadImageUrl', $uploadImageUrl);
        if($gdprManager->getExtensionTitle() == 'gdpr-extensions-com-tiktok-2clicksolution'){
            $this->view->assign('tiktok', 1);
        }
        if(strpos($gdprManager->getExtensionTitle(), 'Google-Review') !== false){
            $this->view->assign('google_review', 1);
        }
        if($gdprManager->getExtensionTitle() == 'gdpr-extensions-com-googlemaps-2clicksolution'){
            $this->view->assign('googlemaps', 1);
        }

        $this->view->assign('gdprManager', $gdprManager);
        $this->view->assign('sites', $configurations);
        return $this->htmlResponse();
    }

    /**
     * action update
     *
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateAction(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager) : \Psr\Http\Message\ResponseInterface
    {
        $locationsData = [];
        if($this->request->hasArgument('tx_gdprextensionscomtiktok_web_gdprextensionscomtiktokgdprmanager')){
            $locationsData = $this->request->getArgument('tx_gdprextensionscomtiktok_web_gdprextensionscomtiktokgdprmanager')['locations'];
        }
        elseif ($this->request->hasArgument('locations')){
            $locationsData = $this->request->getArgument('locations');
        }
        $gdprManager->clearLocations();
        foreach ($locationsData as $locationData) {
            if (!$locationData['lat'] || !$locationData['lon']) {
                continue;
            }
            $location = new MapLocation();
            $location->setTitle($locationData['title']);
            $location->setAddress($locationData['address']);
            $location->setLat((int)($locationData['lat']*1000000));
            $location->setLon((int)($locationData['lon']*1000000));

            $gdprManager->addLocation($location);
        }

        $this->gdprManagerRepository->update($gdprManager);
        return  $this->redirect('list');
    }

    /**
     * action delete
     *
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager
     */
    public function deleteAction(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Model\GdprManager $gdprManager)
    {
        $this->gdprManagerRepository->remove($gdprManager);
        $this->redirect('list');
    }

    /**
     * action uploadImage
     *
     */
    public function uploadImageAction()
    {

        $forCookieWidget = $this->request->getParsedBody()['forCookie'] ?? $this->request->getQueryParams()['forCookie'] ?? null;
        if($forCookieWidget){
            $cookieWidgetImageValue = $this->request->getParsedBody()['cookieWidgetImageValue'] ?? $this->request->getQueryParams()['cookieWidgetImageValue'] ?? null;
            $cookieWidgetPositionValue = $this->request->getParsedBody()['cookieWidgetPositionValue'] ?? $this->request->getQueryParams()['cookieWidgetPositionValue'] ?? null;

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_gdprextensionscomyoutube_domain_model_cookiewidget');
            $queryBuilder
                ->delete('tx_gdprextensionscomyoutube_domain_model_cookiewidget')
                ->execute();

            $queryBuilder
                ->insert('tx_gdprextensionscomyoutube_domain_model_cookiewidget')
                ->values([
                    'cookie_widget_image' => $cookieWidgetImageValue,
                    'cookie_widget_position' => $cookieWidgetPositionValue,
                ])
                ->execute();

            return $this->jsonResponse(json_encode(['status' => true]));
        }else{

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

            $twoClickFolder = Environment::getPublicPath().'/fileadmin/user_upload/two_click_solution/';
            if (!is_dir($twoClickFolder)) {
                mkdir($twoClickFolder);
            }
            $basePath = 'fileadmin/user_upload/two_click_solution/';


            $originalFileName = basename($_FILES['image']['name']);
            $filePath = $_FILES['image']['tmp_name'];
            $fileHash = md5_file($filePath);
            $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            $newFileName = $fileHash . '.' . $fileExtension;

            $targetPath = $twoClickFolder . $newFileName;

            if (move_uploaded_file($filePath, $targetPath)) {
                $siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

                return $this->jsonResponse(json_encode([
                    'url' => $basePath.$newFileName
                ]));
            }
        }
        }

        return $this->jsonResponse(json_encode(['status' => false]));
    }



    /**
     * @param \GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository $gdprManagerRepository
     */
    public function injectGdprManagerRepository(\GdprExtensionsCom\GdprExtensionsComTiktok\Domain\Repository\GdprManagerRepository $gdprManagerRepository)
    {
        $this->gdprManagerRepository = $gdprManagerRepository;
    }
}
