# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```
```php -f bin/magento setup:install ```

# Usage

``` php bin/magento generate:code Module_Name --table="table_name" --type=code-type --name=ModelName ```

if you have created the db_schema file for the table the above command will automatically create setter and getter all the columns in the schema.