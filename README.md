Library to embed [Metabase](http://www.metabase.com/) frames

# Installation
- Install via composer
`composer require ipeevski/metabase-php`
- Go to Metabase and enable embedding - https://[metabase_url]/admin/settings/embedding_in_other_applications
- Note down the Metabase base url and the Embedding secret key

# Usage
## Basic usage

First, you need to find the dashboard or question you want to embed. Note down the id - it would be at the end of the URL (for example https://[metabase_url]/dashboard/1?date=past26weeks

Note the integer after /dashboard/ - that's the ID of the dashboard.
Also note the GET parameters at the end of the url - those are parameters you might want to pass to the dashboard too.


```
<?php
include 'vendor/autoload.php';

// The url of the metabase installation
$metabaseUrl = '[metabase_url]';
// The secret embed key from the admin settings screen
$metabaseKey = '[metabase_key]';
// The id of the dashboard (from the url)
$dashboardId = 1;
// Any parameters to pass to the dashboard
$params = ['date' => 'past26weeks'];

$metabase = new \Metabase\Embed($metabaseUrl, $metabaseKey);
// Generate the HTML to create an iframe with the embedded dashboard
echo $metabase->dashboardIframe($dashboardId, $params);
```
