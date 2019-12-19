# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```

``` php -f bin/magento setup:upgrade ```

# Usage

- To create new Module

``` php bin/magento generate:code Module_Name --type=new-module  ```

- To create models

``` php bin/magento generate:code Module_Name --table="table_name" --type=model --name=ModelName ```

If you have created the db_schema file for the table in the above command, it will automatically create setter and getter of all the columns in the schema.

- To create repositories

``` php bin/magento generate:code Module_Name --type=repository --name=RepositoryClassName --model-class=ModelClassFullName --collection-class=CollectionClassFullName ```

- To create controller

``` php bin/magento generate:code Module_Name --type=controller --name=ControllerName --area=frontend|adminhtml --path=RelativeToModuleControolerFolder ```

- To create helper

``` php bin/magento generate:code Module_Name --type=helper --name=HelperName  ```

- To generate payment method

``` php bin/magento generate:code Module_Name --type=payment --name=MethodName  ```

- To Generate shipping method

``` php bin/magento generate:code Webkul_Test --type=shipping --shipping-code=custom_shipping ```

- To Generate Plugin

``` php bin/magento generate:code Webkul_Test --type=plugin --name=PluginName --plugin=FullClassName [--area=frontend|adminhtml]  ```

- To create observer

``` php bin/magento generate:code Module_Name --type=observer --name=ObserverName --event=event_name [--area=frontend|adminhtml]  ```

- To create cron

``` php bin/magento generate:code Module_Name --type=cron --name=CronName [--schedule="0 1 * * *"]  ```