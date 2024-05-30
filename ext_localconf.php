<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'GdprExtensionsComTiktok',
        'gdprtiktok',
        [
            \GdprExtensionsCom\GdprExtensionsComTiktok\Controller\GdprTiktokController::class => 'index'
        ],
        // non-cacheable actions
        [
            \GdprExtensionsCom\GdprExtensionsComTiktok\Controller\GdprTiktokController::class => '',
            \GdprExtensionsCom\GdprExtensionsComTiktok\Controller\GdprManagerController::class => 'create, update, delete'
        ],
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    // register plugin for cookie widget
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'GdprExtensionsComTiktok',
        'gdprcookiewidget',
        [
            \GdprExtensionsCom\GdprExtensionsComTiktok\Controller\GdprCookieWidgetController::class => 'index'
        ],
        // non-cacheable actions
        [],
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    gdprcookiewidget {
                        iconIdentifier = gdpr_extensions_com_tiktok-plugin-gdprtiktok
                        title = cookie
                        description = LLL:EXT:gdpr_extensions_com_tiktok/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_tiktok_gdprtiktok.description
                        tt_content_defValues {
                            CType = list
                            list_type = gdprextensionscomtiktok_gdprcookiewidget
                        }
                    }
                }
                show = *
            }
       }'
    );
    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod.wizards.newContentElement.wizardItems {
               gdpr.header = LLL:EXT:gdpr_extensions_com_tiktok/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_tiktok_gdprtiktok.name.tab
        }'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.gdpr {
                elements {
                    gdprtiktok {
                        iconIdentifier = gdpr_extensions_com_tiktok-plugin-gdprtiktok
                        title = LLL:EXT:gdpr_extensions_com_tiktok/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_tiktok_gdprtiktok.name
                        description = LLL:EXT:gdpr_extensions_com_tiktok/Resources/Private/Language/locallang_db.xlf:tx_gdpr_extensions_com_tiktok_gdprtiktok.description
                        tt_content_defValues {
                            CType = gdprextensionscomtiktok_gdprtiktok
                        }
                    }
                }
                show = *
            }
       }'
    );
})();
