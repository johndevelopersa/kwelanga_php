# OpenAPIClient-php

The specification for Skynamo's public API <br><br>Helpful links<br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671335262749-Creating-a-Public-API-Key\" id=\"hint_box\">Creating a Public API Key</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671463933597-Postman-Examples\" id=\"hint_box\">Postman examples</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671240071453-How-to-upload-customer-images-using-Postman\" id=\"hint_box\">How to upload customer images</a>


## Installation & Usage

### Requirements

PHP 7.2 and later.

### Composer

To install the bindings via [Composer](https://getcomposer.org/), add the following to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/GIT_USER_ID/GIT_REPO_ID.git"
    }
  ],
  "require": {
    "GIT_USER_ID/GIT_REPO_ID": "*@dev"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
<?php
require_once('/path/to/OpenAPIClient-php/vendor/autoload.php');
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CompletedFormsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->completedformsGet($x_api_client, $page_number, $page_size, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CompletedFormsApi->completedformsGet: ', $e->getMessage(), PHP_EOL;
}

```

## API Endpoints

All URIs are relative to *https://api.za.skynamo.me/v1*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*CompletedFormsApi* | [**completedformsGet**](docs/Api/CompletedFormsApi.md#completedformsget) | **GET** /completedforms | List completed forms
*CompletedFormsApi* | [**completedformsIdGet**](docs/Api/CompletedFormsApi.md#completedformsidget) | **GET** /completedforms/{id} | Get a completed form
*ConfigurationsApi* | [**configurationsGet**](docs/Api/ConfigurationsApi.md#configurationsget) | **GET** /configurations | Get all configuration information
*ContactsApi* | [**contactsGet**](docs/Api/ContactsApi.md#contactsget) | **GET** /contacts | List contacts
*ContactsApi* | [**contactsIdGet**](docs/Api/ContactsApi.md#contactsidget) | **GET** /contacts/{id} | Get a contact
*ContactsApi* | [**contactsPatch**](docs/Api/ContactsApi.md#contactspatch) | **PATCH** /contacts | Update contacts
*ContactsApi* | [**contactsPost**](docs/Api/ContactsApi.md#contactspost) | **POST** /contacts | Create contacts
*ContactsApi* | [**contactsPut**](docs/Api/ContactsApi.md#contactsput) | **PUT** /contacts | Replace contacts
*CreditRequestsApi* | [**creditrequestsGet**](docs/Api/CreditRequestsApi.md#creditrequestsget) | **GET** /creditrequests | List credit requests
*CreditRequestsApi* | [**creditrequestsIdGet**](docs/Api/CreditRequestsApi.md#creditrequestsidget) | **GET** /creditrequests/{id} | Get a credit request
*CreditRequestsApi* | [**creditrequestsPost**](docs/Api/CreditRequestsApi.md#creditrequestspost) | **POST** /creditrequests | Create credit requests
*CustomerCommentsApi* | [**customercommentsGet**](docs/Api/CustomerCommentsApi.md#customercommentsget) | **GET** /customercomments | List customer comments
*CustomerCommentsApi* | [**customercommentsIdGet**](docs/Api/CustomerCommentsApi.md#customercommentsidget) | **GET** /customercomments/{id} | Get a customer comment
*CustomerCommentsApi* | [**customercommentsPost**](docs/Api/CustomerCommentsApi.md#customercommentspost) | **POST** /customercomments | Create customer comments
*CustomersApi* | [**customersGet**](docs/Api/CustomersApi.md#customersget) | **GET** /customers | List customers
*CustomersApi* | [**customersIdGet**](docs/Api/CustomersApi.md#customersidget) | **GET** /customers/{id} | Get a customer
*CustomersApi* | [**customersPatch**](docs/Api/CustomersApi.md#customerspatch) | **PATCH** /customers | Update customers
*CustomersApi* | [**customersPost**](docs/Api/CustomersApi.md#customerspost) | **POST** /customers | Create customers
*CustomersApi* | [**customersPut**](docs/Api/CustomersApi.md#customersput) | **PUT** /customers | Replace customers
*CustomfieldsApi* | [**customfieldsPatch**](docs/Api/CustomfieldsApi.md#customfieldspatch) | **PATCH** /customfields | Update customfields
*DealGroupsApi* | [**dealgroupsGet**](docs/Api/DealGroupsApi.md#dealgroupsget) | **GET** /dealgroups | List deal groups
*DealGroupsApi* | [**dealgroupsIdGet**](docs/Api/DealGroupsApi.md#dealgroupsidget) | **GET** /dealgroups/{id} | Get a deal group
*FilesApi* | [**filesGuidGet**](docs/Api/FilesApi.md#filesguidget) | **GET** /files/{guid} | Get a file
*FilesApi* | [**filesPost**](docs/Api/FilesApi.md#filespost) | **POST** /files | Create file
*FormDefinitionsApi* | [**formdefinitionsGet**](docs/Api/FormDefinitionsApi.md#formdefinitionsget) | **GET** /formdefinitions | List form definitions
*FormDefinitionsApi* | [**formdefinitionsIdGet**](docs/Api/FormDefinitionsApi.md#formdefinitionsidget) | **GET** /formdefinitions/{id} | Get a form definition
*IntegrationFormValuesApi* | [**integrationformvaluesGet**](docs/Api/IntegrationFormValuesApi.md#integrationformvaluesget) | **GET** /integrationformvalues | Get integration form values
*IntegrationsApi* | [**integrationsPost**](docs/Api/IntegrationsApi.md#integrationspost) | **POST** /integrations | Execute an integration action
*InteractionsApi* | [**interactionsGet**](docs/Api/InteractionsApi.md#interactionsget) | **GET** /interactions | List interactions
*InteractionsApi* | [**interactionsIdGet**](docs/Api/InteractionsApi.md#interactionsidget) | **GET** /interactions/{id} | Get an interaction
*InvoicesApi* | [**invoicesDelete**](docs/Api/InvoicesApi.md#invoicesdelete) | **DELETE** /invoices | Delete existing invoice
*InvoicesApi* | [**invoicesGet**](docs/Api/InvoicesApi.md#invoicesget) | **GET** /invoices | List invoices
*InvoicesApi* | [**invoicesIdGet**](docs/Api/InvoicesApi.md#invoicesidget) | **GET** /invoices/{id} | Get an invoice
*InvoicesApi* | [**invoicesPatch**](docs/Api/InvoicesApi.md#invoicespatch) | **PATCH** /invoices | Update invoices
*InvoicesApi* | [**invoicesPost**](docs/Api/InvoicesApi.md#invoicespost) | **POST** /invoices | Create invoices
*InvoicesApi* | [**invoicesPut**](docs/Api/InvoicesApi.md#invoicesput) | **PUT** /invoices | Replace invoices
*InvoicesByExternalIDApi* | [**invoicesbyexternalidDelete**](docs/Api/InvoicesByExternalIDApi.md#invoicesbyexternaliddelete) | **DELETE** /invoicesbyexternalid | Delete existing invoices
*InvoicesByExternalIDApi* | [**invoicesbyexternalidExternalIDGet**](docs/Api/InvoicesByExternalIDApi.md#invoicesbyexternalidexternalidget) | **GET** /invoicesbyexternalid/{ExternalID} | Get an invoice
*InvoicesByExternalIDApi* | [**invoicesbyexternalidPatch**](docs/Api/InvoicesByExternalIDApi.md#invoicesbyexternalidpatch) | **PATCH** /invoicesbyexternalid | Update invoices
*InvoicesByExternalIDApi* | [**invoicesbyexternalidPost**](docs/Api/InvoicesByExternalIDApi.md#invoicesbyexternalidpost) | **POST** /invoicesbyexternalid | Create invoices
*InvoicesByExternalIDApi* | [**invoicesbyexternalidPut**](docs/Api/InvoicesByExternalIDApi.md#invoicesbyexternalidput) | **PUT** /invoicesbyexternalid | Replace invoices
*OrdersApi* | [**ordersGet**](docs/Api/OrdersApi.md#ordersget) | **GET** /orders | List orders
*OrdersApi* | [**ordersIdGet**](docs/Api/OrdersApi.md#ordersidget) | **GET** /orders/{id} | Get an order
*OrdersApi* | [**ordersPost**](docs/Api/OrdersApi.md#orderspost) | **POST** /orders | Create orders
*PriceListsApi* | [**pricelistsGet**](docs/Api/PriceListsApi.md#pricelistsget) | **GET** /pricelists | List prices lists
*PriceListsApi* | [**pricelistsIdGet**](docs/Api/PriceListsApi.md#pricelistsidget) | **GET** /pricelists/{id} | Get a price list
*PriceListsApi* | [**pricelistsPatch**](docs/Api/PriceListsApi.md#pricelistspatch) | **PATCH** /pricelists | Update price lists
*PriceListsApi* | [**pricelistsPost**](docs/Api/PriceListsApi.md#pricelistspost) | **POST** /pricelists | Create price lists
*PriceListsApi* | [**pricelistsPut**](docs/Api/PriceListsApi.md#pricelistsput) | **PUT** /pricelists | Replace price lists
*PricesApi* | [**pricesGet**](docs/Api/PricesApi.md#pricesget) | **GET** /prices | List prices
*PricesApi* | [**pricesPost**](docs/Api/PricesApi.md#pricespost) | **POST** /prices | Create/Update order unit price
*ProductsApi* | [**productsGet**](docs/Api/ProductsApi.md#productsget) | **GET** /products | List products
*ProductsApi* | [**productsIdGet**](docs/Api/ProductsApi.md#productsidget) | **GET** /products/{id} | Get a product
*ProductsApi* | [**productsPatch**](docs/Api/ProductsApi.md#productspatch) | **PATCH** /products | Update products
*ProductsApi* | [**productsPost**](docs/Api/ProductsApi.md#productspost) | **POST** /products | Create products
*ProductsApi* | [**productsPut**](docs/Api/ProductsApi.md#productsput) | **PUT** /products | Replace products
*QuotesApi* | [**quotesGet**](docs/Api/QuotesApi.md#quotesget) | **GET** /quotes | List quotes
*QuotesApi* | [**quotesIdGet**](docs/Api/QuotesApi.md#quotesidget) | **GET** /quotes/{id} | Get a quote
*QuotesApi* | [**quotesPost**](docs/Api/QuotesApi.md#quotespost) | **POST** /quotes | Create quotes
*ScheduledVisitsApi* | [**scheduledvisitsDelete**](docs/Api/ScheduledVisitsApi.md#scheduledvisitsdelete) | **DELETE** /scheduledvisits | Delete existing scheduled visits
*ScheduledVisitsApi* | [**scheduledvisitsGet**](docs/Api/ScheduledVisitsApi.md#scheduledvisitsget) | **GET** /scheduledvisits | List scheduled visits
*ScheduledVisitsApi* | [**scheduledvisitsIdGet**](docs/Api/ScheduledVisitsApi.md#scheduledvisitsidget) | **GET** /scheduledvisits/{id} | Get a scheduled visit
*ScheduledVisitsApi* | [**scheduledvisitsPatch**](docs/Api/ScheduledVisitsApi.md#scheduledvisitspatch) | **PATCH** /scheduledvisits | Update scheduled visits
*ScheduledVisitsApi* | [**scheduledvisitsPost**](docs/Api/ScheduledVisitsApi.md#scheduledvisitspost) | **POST** /scheduledvisits | Create scheduled visits
*ScheduledVisitsApi* | [**scheduledvisitsPut**](docs/Api/ScheduledVisitsApi.md#scheduledvisitsput) | **PUT** /scheduledvisits | Replace scheduled visits
*StockLevelsApi* | [**stocklevelsGet**](docs/Api/StockLevelsApi.md#stocklevelsget) | **GET** /stocklevels | List stock levels
*StockLevelsApi* | [**stocklevelsPost**](docs/Api/StockLevelsApi.md#stocklevelspost) | **POST** /stocklevels | Create/Update stock levels
*TasksApi* | [**tasksDelete**](docs/Api/TasksApi.md#tasksdelete) | **DELETE** /tasks | Delete existing tasks
*TasksApi* | [**tasksGet**](docs/Api/TasksApi.md#tasksget) | **GET** /tasks | List tasks
*TasksApi* | [**tasksIdGet**](docs/Api/TasksApi.md#tasksidget) | **GET** /tasks/{id} | Get a task
*TasksApi* | [**tasksPatch**](docs/Api/TasksApi.md#taskspatch) | **PATCH** /tasks | Update tasks
*TasksApi* | [**tasksPost**](docs/Api/TasksApi.md#taskspost) | **POST** /tasks | Create tasks
*TasksApi* | [**tasksPut**](docs/Api/TasksApi.md#tasksput) | **PUT** /tasks | Replace tasks
*TaxRatesApi* | [**taxratesGet**](docs/Api/TaxRatesApi.md#taxratesget) | **GET** /taxrates | List tax rates
*TaxRatesApi* | [**taxratesIdGet**](docs/Api/TaxRatesApi.md#taxratesidget) | **GET** /taxrates/{id} | Get a tax rate
*TaxRatesApi* | [**taxratesPatch**](docs/Api/TaxRatesApi.md#taxratespatch) | **PATCH** /taxrates | Update tax rates
*TaxRatesApi* | [**taxratesPost**](docs/Api/TaxRatesApi.md#taxratespost) | **POST** /taxrates | Create tax rates
*TaxRatesApi* | [**taxratesPut**](docs/Api/TaxRatesApi.md#taxratesput) | **PUT** /taxrates | Replace tax rates
*UsersApi* | [**usersGet**](docs/Api/UsersApi.md#usersget) | **GET** /users | List users
*UsersApi* | [**usersIdGet**](docs/Api/UsersApi.md#usersidget) | **GET** /users/{id} | Get an user
*VisitFrequenciesApi* | [**visitfrequenciesGet**](docs/Api/VisitFrequenciesApi.md#visitfrequenciesget) | **GET** /visitfrequencies | List visit frequencies
*VisitFrequenciesApi* | [**visitfrequenciesIdGet**](docs/Api/VisitFrequenciesApi.md#visitfrequenciesidget) | **GET** /visitfrequencies/{id} | Get a visit frequency
*VisitFrequenciesApi* | [**visitfrequenciesPatch**](docs/Api/VisitFrequenciesApi.md#visitfrequenciespatch) | **PATCH** /visitfrequencies | Update visit frequencies
*VisitFrequenciesApi* | [**visitfrequenciesPost**](docs/Api/VisitFrequenciesApi.md#visitfrequenciespost) | **POST** /visitfrequencies | Create visit frequencies
*VisitFrequenciesApi* | [**visitfrequenciesPut**](docs/Api/VisitFrequenciesApi.md#visitfrequenciesput) | **PUT** /visitfrequencies | Replace visit frequencies
*WarehousesApi* | [**warehousesGet**](docs/Api/WarehousesApi.md#warehousesget) | **GET** /warehouses | List warehouses
*WarehousesApi* | [**warehousesIdGet**](docs/Api/WarehousesApi.md#warehousesidget) | **GET** /warehouses/{id} | Get a warehouse
*WarehousesApi* | [**warehousesPatch**](docs/Api/WarehousesApi.md#warehousespatch) | **PATCH** /warehouses | Update warehouses
*WarehousesApi* | [**warehousesPost**](docs/Api/WarehousesApi.md#warehousespost) | **POST** /warehouses | Create warehouses
*WarehousesApi* | [**warehousesPut**](docs/Api/WarehousesApi.md#warehousesput) | **PUT** /warehouses | Replace warehouses

## Models

- [CompletedForm](docs/Model/CompletedForm.md)
- [Configuration](docs/Model/Configuration.md)
- [Contact](docs/Model/Contact.md)
- [ContactPatch](docs/Model/ContactPatch.md)
- [ContactPost](docs/Model/ContactPost.md)
- [ContactPut](docs/Model/ContactPut.md)
- [CreditRequest](docs/Model/CreditRequest.md)
- [CreditRequestFormCustomFields](docs/Model/CreditRequestFormCustomFields.md)
- [CreditRequestForms](docs/Model/CreditRequestForms.md)
- [CreditRequestItem](docs/Model/CreditRequestItem.md)
- [CreditRequestItemPost](docs/Model/CreditRequestItemPost.md)
- [CreditRequestPost](docs/Model/CreditRequestPost.md)
- [Currency](docs/Model/Currency.md)
- [CustomField](docs/Model/CustomField.md)
- [CustomFieldEnumeratorComment](docs/Model/CustomFieldEnumeratorComment.md)
- [Customer](docs/Model/Customer.md)
- [CustomerComment](docs/Model/CustomerComment.md)
- [CustomerCommentPost](docs/Model/CustomerCommentPost.md)
- [CustomerPatch](docs/Model/CustomerPatch.md)
- [CustomerPost](docs/Model/CustomerPost.md)
- [CustomerPut](docs/Model/CustomerPut.md)
- [CustomfieldPatch](docs/Model/CustomfieldPatch.md)
- [DealGroup](docs/Model/DealGroup.md)
- [DealGroupItem](docs/Model/DealGroupItem.md)
- [DistanceUnit](docs/Model/DistanceUnit.md)
- [EnumGrowData](docs/Model/EnumGrowData.md)
- [ErrorModel](docs/Model/ErrorModel.md)
- [ErrorModelErrors](docs/Model/ErrorModelErrors.md)
- [FieldGrowData](docs/Model/FieldGrowData.md)
- [File](docs/Model/File.md)
- [FilePost](docs/Model/FilePost.md)
- [FormDefinition](docs/Model/FormDefinition.md)
- [FormDefinitionCustomField](docs/Model/FormDefinitionCustomField.md)
- [FormDefinitionCustomFieldEnumerator](docs/Model/FormDefinitionCustomFieldEnumerator.md)
- [InlineResponse200](docs/Model/InlineResponse200.md)
- [InlineResponse2001](docs/Model/InlineResponse2001.md)
- [InlineResponse20010](docs/Model/InlineResponse20010.md)
- [InlineResponse20010Data](docs/Model/InlineResponse20010Data.md)
- [InlineResponse20011](docs/Model/InlineResponse20011.md)
- [InlineResponse20012](docs/Model/InlineResponse20012.md)
- [InlineResponse20012Data](docs/Model/InlineResponse20012Data.md)
- [InlineResponse20013](docs/Model/InlineResponse20013.md)
- [InlineResponse20014](docs/Model/InlineResponse20014.md)
- [InlineResponse20014FieldsAdded](docs/Model/InlineResponse20014FieldsAdded.md)
- [InlineResponse20015](docs/Model/InlineResponse20015.md)
- [InlineResponse20016](docs/Model/InlineResponse20016.md)
- [InlineResponse20016Data](docs/Model/InlineResponse20016Data.md)
- [InlineResponse20017](docs/Model/InlineResponse20017.md)
- [InlineResponse20018](docs/Model/InlineResponse20018.md)
- [InlineResponse20019](docs/Model/InlineResponse20019.md)
- [InlineResponse2002](docs/Model/InlineResponse2002.md)
- [InlineResponse20020](docs/Model/InlineResponse20020.md)
- [InlineResponse20021](docs/Model/InlineResponse20021.md)
- [InlineResponse20021Data](docs/Model/InlineResponse20021Data.md)
- [InlineResponse20022](docs/Model/InlineResponse20022.md)
- [InlineResponse20023](docs/Model/InlineResponse20023.md)
- [InlineResponse20024](docs/Model/InlineResponse20024.md)
- [InlineResponse20025](docs/Model/InlineResponse20025.md)
- [InlineResponse20026](docs/Model/InlineResponse20026.md)
- [InlineResponse20026Data](docs/Model/InlineResponse20026Data.md)
- [InlineResponse20027](docs/Model/InlineResponse20027.md)
- [InlineResponse20028](docs/Model/InlineResponse20028.md)
- [InlineResponse20029](docs/Model/InlineResponse20029.md)
- [InlineResponse2003](docs/Model/InlineResponse2003.md)
- [InlineResponse20030](docs/Model/InlineResponse20030.md)
- [InlineResponse20030Data](docs/Model/InlineResponse20030Data.md)
- [InlineResponse20031](docs/Model/InlineResponse20031.md)
- [InlineResponse2003Data](docs/Model/InlineResponse2003Data.md)
- [InlineResponse2004](docs/Model/InlineResponse2004.md)
- [InlineResponse2005](docs/Model/InlineResponse2005.md)
- [InlineResponse2006](docs/Model/InlineResponse2006.md)
- [InlineResponse2006Data](docs/Model/InlineResponse2006Data.md)
- [InlineResponse2007](docs/Model/InlineResponse2007.md)
- [InlineResponse2008](docs/Model/InlineResponse2008.md)
- [InlineResponse2008Data](docs/Model/InlineResponse2008Data.md)
- [InlineResponse2009](docs/Model/InlineResponse2009.md)
- [IntegrationFormValues](docs/Model/IntegrationFormValues.md)
- [IntegrationRequest](docs/Model/IntegrationRequest.md)
- [Interaction](docs/Model/Interaction.md)
- [Invoice](docs/Model/Invoice.md)
- [InvoiceItem](docs/Model/InvoiceItem.md)
- [InvoicePatch](docs/Model/InvoicePatch.md)
- [InvoicePost](docs/Model/InvoicePost.md)
- [InvoicePut](docs/Model/InvoicePut.md)
- [Location](docs/Model/Location.md)
- [Order](docs/Model/Order.md)
- [OrderFormCustomFields](docs/Model/OrderFormCustomFields.md)
- [OrderForms](docs/Model/OrderForms.md)
- [OrderItem](docs/Model/OrderItem.md)
- [OrderItemPost](docs/Model/OrderItemPost.md)
- [OrderPost](docs/Model/OrderPost.md)
- [OrderUnit](docs/Model/OrderUnit.md)
- [OrderUnitPrice](docs/Model/OrderUnitPrice.md)
- [OrderUnitPricePost](docs/Model/OrderUnitPricePost.md)
- [PagingResponse](docs/Model/PagingResponse.md)
- [PriceList](docs/Model/PriceList.md)
- [PriceListPatch](docs/Model/PriceListPatch.md)
- [PriceListPost](docs/Model/PriceListPost.md)
- [PriceListPut](docs/Model/PriceListPut.md)
- [Product](docs/Model/Product.md)
- [ProductPatch](docs/Model/ProductPatch.md)
- [ProductPost](docs/Model/ProductPost.md)
- [ProductPut](docs/Model/ProductPut.md)
- [Quote](docs/Model/Quote.md)
- [QuoteFormCustomFields](docs/Model/QuoteFormCustomFields.md)
- [QuoteForms](docs/Model/QuoteForms.md)
- [QuoteItem](docs/Model/QuoteItem.md)
- [QuoteItemPost](docs/Model/QuoteItemPost.md)
- [QuotePost](docs/Model/QuotePost.md)
- [ScheduledVisit](docs/Model/ScheduledVisit.md)
- [ScheduledVisitPatch](docs/Model/ScheduledVisitPatch.md)
- [ScheduledVisitPost](docs/Model/ScheduledVisitPost.md)
- [ScheduledVisitPut](docs/Model/ScheduledVisitPut.md)
- [StockLevel](docs/Model/StockLevel.md)
- [StockLevelPost](docs/Model/StockLevelPost.md)
- [Task](docs/Model/Task.md)
- [TaskPatch](docs/Model/TaskPatch.md)
- [TaskPost](docs/Model/TaskPost.md)
- [TaskPut](docs/Model/TaskPut.md)
- [TaxRate](docs/Model/TaxRate.md)
- [TaxRatePatch](docs/Model/TaxRatePatch.md)
- [TaxRatePost](docs/Model/TaxRatePost.md)
- [TaxRatePut](docs/Model/TaxRatePut.md)
- [TimeZone](docs/Model/TimeZone.md)
- [User](docs/Model/User.md)
- [VisitFrequency](docs/Model/VisitFrequency.md)
- [VisitFrequencyPatch](docs/Model/VisitFrequencyPatch.md)
- [VisitFrequencyPost](docs/Model/VisitFrequencyPost.md)
- [VisitFrequencyPut](docs/Model/VisitFrequencyPut.md)
- [Warehouse](docs/Model/Warehouse.md)
- [WarehousePatch](docs/Model/WarehousePatch.md)
- [WarehousePost](docs/Model/WarehousePost.md)
- [WarehousePut](docs/Model/WarehousePut.md)

## Authorization

### api_key

- **Type**: API key
- **API key parameter name**: x-api-key
- **Location**: HTTP header


## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```

## Author



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `1.0.18`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`
