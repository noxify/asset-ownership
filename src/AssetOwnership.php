<?php
/**
 * Asset Ownership plugin for Craft CMS 3.x
 *
 * This plugin extends the Asset Query to limit the shown assets.
 *
 * @link      https://github.com/noxify
 * @copyright Copyright (c) 2018 Marcus Reinhardt
 * @builtwith https://pluginfactory.io/
 */

namespace noxify\assetownership;

use noxify\assetownership\models\Settings;

use Craft;
use yii\base\Event;
use craft\helpers\Db;
use craft\base\Plugin;
use craft\helpers\Assets;
use yii\db\BaseActiveRecord;
use craft\elements\db\AssetQuery;
use craft\services\UserPermissions;
use craft\elements\Asset as AssetElement;
use craft\records\Asset as AssetRecord;
use craft\events\RegisterUserPermissionsEvent;
use noxify\assetownership\models\Settings as AssetOwnershipSettings;
/**
 * Class AssetOwnership
 *
 * @author    Marcus Reinhardt
 * @package   AssetOwnership
 * @since     1.0.1
 *
 */
class AssetOwnership extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AssetOwnership
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.1';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        //create new permission 
        //code powered by https://docs.craftcms.com/v3/updating-plugins.html#registerUserPermissions
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[\Craft::t('asset-ownership', 'Asset Ownership')] = [
                'viewPeerAssets' => ['label' => \Craft::t('asset-ownership', 'Edit other authors’ assets')]
            ];
        });

        //filter assets if the user is not an admin or has the permission to see assets from other users
        if( !Craft::$app->user->getIsAdmin() || !Craft::$app->user->checkPermission('viewPeerAssets') ) {
            
            Event::on('*', AssetQuery::EVENT_AFTER_PREPARE, function($event) {
                if( $event->sender instanceof AssetQuery ) {
                    $event->sender->query->innerJoin('{{%relations}} relations', '[[assets.id]] = [[relations.sourceId]]');
                    $event->sender->query->andWhere(Db::parseParam('relations.targetId', Craft::$app->user->getId()));
                }
            });
        }

        if( $this->settings->uniqueAssetFilename ) {
            Event::on(AssetElement::class, AssetElement::EVENT_BEFORE_HANDLE_FILE, function($event) {

                $asset = $event->asset;
                
                preg_match('/{(.*)}(.*)/', $asset->newLocation, $result, PREG_OFFSET_CAPTURE, 0);

                $folder = $result[1][0];
                $newFilename = md5(time().'_'.Craft::$app->user->getId()).'_'.$result[2][0];
                $asset->title =  Assets::filename2Title(pathinfo($result[2][0], PATHINFO_FILENAME));
            
                $asset->newLocation = '{'.$folder.'}'.$newFilename;

            });
        }

        

        //create relationship between user and asset
        //don't repeat yourself - most of the event code is copied from
        //https://github.com/page-8/craft-manytomany/
        //thanks guys <3
        Event::on('*', BaseActiveRecord::EVENT_AFTER_INSERT, function($event) {

            if( $event->sender instanceof AssetRecord && $this->settings->assetUser) {
                
                $columns = [
                    'fieldId' => Craft::$app->fields->getFieldByHandle($this->settings->assetUser)->id,
                    'sourceId' => $event->sender->id,
                    'sourceSiteId' => null,
                    'targetId' => Craft::$app->user->getId(),
                    'sortOrder' => 1,
                ];
                
                Craft::$app->db->createCommand()->insert('{{%relations}}', $columns)->execute();
            }

            $event->handled = true;
        });

        Craft::info(
            Craft::t(
                'asset-ownership',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new AssetOwnershipSettings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        $allFields = Craft::$app->fields->getAllFields() ?? [];
        
        $fields = [];
        foreach ($allFields as $field) {
            $fields[$field->handle] = $field->name;
        }

        return Craft::$app->view->renderTemplate(
            'asset-ownership/settings',
            [
                'settings' => $this->getSettings(),
                'fields' => $fields
            ]
        );
    }
}
