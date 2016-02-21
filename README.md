# CMS Version Checker

[![Build Status](https://travis-ci.org/cpeter/php-qkeylm-email-notification.svg?branch=master)](https://travis-ci.org/cpeter/php-qkeylm-email-notification)
[![Latest Stable Version](https://poser.pugx.org/cpeter/php-qkeylm-email-notification/v/stable.svg)](https://packagist.org/packages/cpeter/php-qkeylm-email-notification)
[![Total Downloads](https://poser.pugx.org/cpeter/php-qkeylm-email-notification/downloads.svg)](https://packagist.org/packages/cpeter/php-qkeylm-email-notification)
[![License](https://poser.pugx.org/cpeter/php-qkeylm-email-notification/license.svg)](https://packagist.org/packages/cpeter/php-qkeylm-email-notification)


Keep track of new releases for different products ...


## Installation

CMS Version Checker can be installed with [Composer](http://getcomposer.org)
by adding it as a dependency to your project's composer.json file.

```json
{
    "require": {
        "cpeter/php-qkeylm-email-notification": "*"
    }
}
```

Please refer to [Composer's documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction)
for more detailed installation and usage instructions.

## Usage

The script can be run with this commnad:

```./bin/php-qkeylm-email-notification notify```

However before you run the command you need to specify some settings in the conf/config.yml file. You will need to
specify the access details to the www.qkenhanced.com.au website, but also the child names you want to be highlighted in
the notification emails.

Add as many recipients you want to the bcc section. From and to needs to be some email address you can use to send 
emails from.

The room name and child name must be specified as well. This can be found out once you logged in to
www.qkenhanced.com.au via browser.

For any questions ro difficulties setting up the script contact the author of this script.

## Development

If you want to contribute to this project feel free to make PR's. 

Definiteluy more unit tests needs to be added to the project, so feel free to make PR's for that.
Any other improvement suggestions as welcome.
