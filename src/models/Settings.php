<?php
/**
 * Asset Ownership plugin for Craft CMS 3.x
 *
 * This plugin extends the Asset Query to limit the shown assets.
 *
 * @link      https://github.com/noxify
 * @copyright Copyright (c) 2018 Marcus Reinhardt
 */

namespace noxify\assetownership\models;

use noxify\assetownership\AssetOwnership;

use Craft;
use craft\base\Model;

/**
 * @author    Marcus Reinhardt
 * @package   AssetOwnership
 * @since     1.0.1
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $assetUser;

    /**
     * @var boolean
     */
    public $uniqueAssetFilename = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['assetUser', 'string'],
            ['uniqueAssetFilename', 'boolean']
        ];
    }
}
