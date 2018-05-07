# Asset Ownership plugin for Craft CMS 3

This plugin extends the existing asset query and shows only the assets which has been uploaded by the current user.
To ensure that an user can not overwrite an asset from another user, this plugin comes with the functionality to create unique asset file names.

## Requirements

This plugin requires Craft CMS 3 or later.

## Installation

### Via Composer

    composer require noxify/asset-ownership

### Via Marketplace

Just search for `Asset Ownership` and click install.

## Configuring Asset Ownership

The Plugin comes with two settings which can be configured via the Control Panel.

| Name                   | Default | Required | 
|------------------------|---------|----------|
| Asset User Field       |  `NULL` | Yes      |
| Create Unique Filename |  `true` | Yes      |


## Using Asset Ownership

After installing and enabling the Plugin, you have to create a new field.

* Fieldname: Uploaded By
* Handle: uploadedBy
* Field Type: Users
* Sources: All [x]
* Limit: 1

After creating the new field, you have assign it to all your volumes.

```
Control Panel > Settings > Assets > Volumes > {volumename} > Tab: Field Layout
``` 

Last but not least, you have to go to Plugin Settings.
Here you have to choose the created "Uploaded By" field in the dropdown.

## How can I see all uploaded assets

To see all assets you must be an `admin` or you have the `custom permission` to see assets from other users.