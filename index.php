<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/vendor/autoload.php';
require 'simple_html_dom.php';
require 'Product.php';

$url = 'http://localhost/amz-scrapper-php/text.html';
$product = new ProductPublic([
	'url' => $url
]);

echo json_encode($product->detail(), JSON_PRETTY_PRINT);

?>
