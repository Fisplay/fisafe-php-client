# Fisafe Api Client Documentation

The `Fisafe Api Client` is a powerful PHP client designed to integrate seamlessly with the `fisafe.cloud` API. This README provides a concise guide on setting up and using the client.

## Installation

To install the Fisafe Api Client, use Composer:

```bash
composer require fisafe/api-client
```

Ensure you have the necessary dependencies installed and autoloaded.

## Initialization

First, include the required files and use the necessary classes:

```php
<?php

use Fisafe\ApiClient;

require('vendor/autoload.php');
```

Initialize the client by passing the desired authentication details with your `fisafe.cloud` credentials:

```php
$fisafeClient = new ApiClient(
    'https://auth.fisafe.cloud',
    'used_realm',
    'used_client_id',
    'your_username'
    'your_password'
);
```

Specify used organization (tenant) domain

```php
$fisafeClient->setApiUrl('https://organization-domain.fisplay.cloud/v1/api/');
```

You can also change the url (organization context) if needed, but the user must be related to the organization.

## Usage

### Creating a User:

```php
$user = $fisafeClient->createUser('my-user-identifier');
echo "User created with ID: {$user->id}" . PHP_EOL;
```

### Creating an Identifier for a User:

You can associate a user with identifiers such as 'pin', 'rfid-tag', or 'licence-plate':

```php
$identifierType = 'rfid-tag';  // Choose from 'pin', 'rfid-tag', or 'licence-plate'
$identifierValue = '121212121';
$identifier = $fisafeClient->createIdentifer($user->id, $identifierValue, $identifierType);
echo "Identifier {$identifierValue} of type {$identifierType} created for User ID: {$user->id}" . PHP_EOL;
```

### Listing Users:

Retrieve a list of users with a specific identifier:

```php
$filteredUsers = $fisafeClient->listUsers(['identifier' => 'my-user-identifier']);
echo "List of users with identifier 'my-user-identifier':" . PHP_EOL;
var_dump($filteredUsers);
```

### Granting User Access (assuming function and context logic exists):

```php
$contextId = 12345;  // Example context ID
$fromDate = new DateTime('now');
$toDate = new DateTime('+1 month');
$access = $fisafeClient->createGrantedAccess($contextId, $user->id, $fromDate, $toDate);
echo "Access granted to context {$contextId} from {$fromDate->format('Y-m-d')} to {$toDate->format('Y-m-d')}" . PHP_EOL;
```

## Conclusion

The `Fisafe Api Client` provides a streamlined approach to interacting with the `fisafe.cloud` API, simplifying the management of users, identifiers, and access rights. For more in-depth details, please refer to the full documentation or the official API documentation.

**Note:** Always handle exceptions and check return values appropriately in production code.