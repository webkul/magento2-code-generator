# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```
```php -f bin/magento setup:install ```

# Usage

``` php bin/magento generate:code Module_Name --table="table_name" --type=code-type --name=ModelName ```

Ff you have created the db_schema file for the table in the above command, it will automatically create setter and getter all the columns in the schema.