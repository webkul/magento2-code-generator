# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```

``` php -f bin/magento setup:update ```

# Usage

- To create new Module

``` php bin/magento generate:code Module_Name –type=new-module  ```

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


