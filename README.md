# Code Generator For Magento2

# Installation

``` composer require webkul/code-generator ```

``` php -f bin/magento setup:upgrade ```

# User Guide

- [Magento 2 Code Generator](https://webkul.com/blog/magento-2-code-generator/)

# Usage

- To create new Module

``` php bin/magento generate:code Module_Name --type=new-module  ```

- To create models

``` php bin/magento generate:code Module_Name --table="table_name" --type=model --name=ModelName ```

If you have created the db_schema file for the table in the above command, it will automatically create setter and getter of all the columns in the schema.

- To create repositories

``` php bin/magento generate:code Module_Name --type=repository --name=RepositoryClassName --model-class=ModelClassFullName --collection-class=CollectionClassFullName ```

- To create controller

``` php bin/magento generate:code Module_Name --type=controller --name=ControllerName --area=frontend|adminhtml --path=RelativeToModuleControolerFolder --router=RouteName ```

- To create helper

``` php bin/magento generate:code Module_Name --type=helper --name=HelperName  ```

- To create payment method

``` php bin/magento generate:code Module_Name --type=payment --name=MethodName --payment-code=PaymentMethodCode ```

- To create shipping method

``` php bin/magento generate:code Module_Name --type=shipping --shipping-code=custom_shipping ```

- To create Plugin

``` php bin/magento generate:code Module_Name --type=plugin --name=PluginName --plugin=FullClassName [--area=frontend|adminhtml]  ```

- To create observer

``` php bin/magento generate:code Module_Name --type=observer --name=ObserverName --event=event_name [--area=frontend|adminhtml]  ```

- To create cron

``` php bin/magento generate:code Module_Name --type=cron --name=CronName [--schedule="0 1 * * *"]  ```


- To create unit test cases

``` php bin/magento generate:code Module_Name --type=unit-test  ```


- To create view

``` php bin/magento generate:code Module_Name --type=create-view --name=webkul_index_index --area=adminhtml --block-class=Main --template=hello.phtml --layout-type=admin-2column-left ```

``` --block-class ```, ``` --template ```, ``` --layout-type ``` are optional.

- To create logger

``` php bin/magento generate:code Module_Name --type=logger [--name=loggerfile]  ```

- To create command

``` php bin/magento generate:code Module_Name --type=command --name=CommandClass --command='test:hello'  ```

- To override/rewrite a class (Block, Model, Controller)

``` php bin/magento generate:code Module_Name --type=rewrite --name=ClassName --rewrite='OverriddenClass' [--path=RelativeToModuleFolder]  ```

- To create email template

``` php bin/magento generate:code Module_Name --type=email --name="Email Label" [--id="module_email_test"] [--template="test"]  ```

- To create Ui Grid Listing

``` php bin/magento generate:code Module_Name --type=ui_component_listing --name="test_test" [--columns_name="test_column"] [--model_class_name="Model Class Name"][--table="Table Name"]   ```

- To create Ui Component Form

``` php bin/magento generate:code Module_Name --type=ui_component_form --name="test_test_form" [--provider_name="Data Provider Name"] [--model_class_name="Model Class Name"] [--fieldset_name="Field Set Name"] [--fieldset_label="Field Set Label"] [--submit_url="Form Submit Url"] [--form_field="Form Fields in Json Format"] ```

Ex: form_field = '[{"field_name": "test_input_field", "field_type": "input", "field_label": "Test Input Field", "is_required": "true"}, {"field_name": "test_image_field", "field_type": "imageUploader", "field_label": "Image Uploader", "is_required": "false", "image_upload_url": "test/test/upload"}]'