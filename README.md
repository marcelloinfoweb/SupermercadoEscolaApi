# Mage2 Module Funarbe SupermercadoEscolaApi

    ``funarbe/module-supermercadoescolaapi``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
API para Separação dos Produtos

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Funarbe`
 - Enable the module by running `php bin/magento module:enable Funarbe_SupermercadoEscolaApi`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require funarbe/module-supermercadoescolaapi`
 - enable the module by running `php bin/magento module:enable Funarbe_SupermercadoEscolaApi`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - API Endpoint
	- GET - Funarbe\SupermercadoEscolaApi\Api\SeparacaoProdutosManagementInterface > Funarbe\SupermercadoEscolaApi\Model\SeparacaoProdutosManagement


## Attributes



