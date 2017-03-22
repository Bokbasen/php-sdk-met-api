# php-sdk-met-api
SDK for working against Bokbasen's Metadata API. Currently only exports are supported.

The SDK assumes working knowledge of the API, see documentation: https://bokbasen.jira.com/wiki/spaces/api/pages/61964298/Metadata


## Authentication

This SDK requires the Auth SDK to work, see [php-sdk-auth](https://github.com/Bokbasen/php-sdk-auth) for details on how to authenticate. 

All code examples below assumes that this variable exists: 

```php
<?php
 $auth = new \Bokbasen\Auth\Login('username', 'password');
 ?>
```

## ONIX Exports

### Create ONIX Export client

Use auth object and set your subscription when creating the export object (subscription is based on your contract with Bokbasen. basic, extended or school)

```php
<?php
use Bokbasen\Metadata\Export\Onix;
$onixClient = new Onix($auth, Onix::URL_PROD, Onix::SUBSCRIPTION_EXTENDED);
?>
```


### Get ONIX for single ISBN
```php
<?php
$onixAsString = $onixClient->getByISBN('9788276749557');
?>
```

### Download ONIX to file based on a date
This is only used when you do not have a valid next token. Given filename will be appended until all pages are fetched (if $downloadAllPages === true)

```php
<?php
$nextToken = $onixClient->downloadAfter(new \DateTime('2017-01-01'),'/onixFolder/');
//Save next token for later use
?>
```

###Download ONIX to file based on a token
Use $nextToken to get all changes since last execution

```php
<?php
//Loop to get all pages
$morePages = true;
while($morePages){
	$morePages = $onixClient->downloadNext($nextToken,'/onixFolder/');
	$nextToken = $onixClient->getLastNextToken();
}

//Save next token for later use
?>
```

## Object Exports

Object reports has a similar process as ONIX download when it comes to paging. The SDK abstracts the report aspect and download the actual files for you.

### Download all objects changes after a certain date
This is only used when you do not have a valid next token.

```php
<?php
$nextToken = $objectClient->downloadAfter(new \DateTime('2017-10-01'),'/pathForObjects/');
//Save next token for later use
?>
```

### Download objects to file based on a token
Use $nextToken to get all changes since last execution

```php
<?php
//Loop to get all pages, each page will be stored as separate file
$morePages = true;
while($morePages){
	$morePages = $onixClient->downloadNext($nextToken,'/pathForObjects/');
	$nextToken = $onixClient->getLastNextToken();
}

//Save next token for later use
?>
```

###Additional filters
```php
<?php
//Only download spesific types of objects
$nextToken = $objectClient->downloadAfter(new \DateTime('2017-10-01'),'/pathForObjects/',[\Bokbasen\Metadata\Export\Object::OBJECT_TYPE_AUDIO_SAMPLE,\Bokbasen\Metadata\Export\Object::OBJECT_COVER_IMAGE_SMALL]);

//you can also download for spesific ISBNs ($downloadAllPages must be true for this to work, and ensure to set a old date)
$objectClient->downloadAfter(new \DateTime('1950-01-01'),'/pathForObjects/',[\Bokbasen\Metadata\Export\Object::OBJECT_TYPE_AUDIO_SAMPLE,\Bokbasen\Metadata\Export\Object::OBJECT_COVER_IMAGE_SMALL],true,['9788251824491','9788215012520']);
?>
```

