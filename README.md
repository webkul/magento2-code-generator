# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```

``` php -f bin/magento setup:update ```

# Usage
- To Create new Module

``` php bin/magento generate:code Module_Name –type=new-module  ```

- To Generate code types

``` php bin/magento generate:code Module_Name --table="table_name" --type=code-type --name=ModelName ```

If you have created the db_schema file for the table in the above command, it will automatically create setter and getter of all the columns in the schema.