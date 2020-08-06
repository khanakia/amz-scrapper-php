<?php
// require 'simple_html_dom.php';
class ProductPublic {
  public function __construct(array $config = []) {
    $this->url = $config['url'];

    $client = new GuzzleHttp\Client();
    //  $res = $client->request('GET', 'http://amazon.in/dp/B00YJJWBUA');
    $res = $client->request('GET', $this->url);
    // echo $res->getStatusCode();
    $this->html = $res->getBody()->getContents();
    $this->doc = str_get_html($this->html);
  }

  function breadCrumb() {
      $breadcrumbs_ = $this->doc->find('#wayfinding-breadcrumbs_feature_div ul li a');
      $breadcrumbs = [];
      foreach ($breadcrumbs_ as $key => $value) {
        // echo $value->plaintext;
        $breadcrumbs[] = [
          'title' => trim($value->plaintext),
          'link' => $value->href,
        ];
      }
      return $breadcrumbs;
  }

  function bulletPoints() {
    $bulletPoints_ = $this->doc->find('#feature-bullets ul li');
    $bulletPoints = [];
    foreach ($bulletPoints_ as $key => $value) {
      // echo $value->plaintext;
      $bulletPoints[] = trim($value->plaintext);
    }

    return $bulletPoints;
  }

  function listVariations() {
    $list = $this->doc->find('#twister ul li');
    $data = [];
    foreach ($list as $key => $value) {
      $title = trim($value->title);
      $title = str_replace('Click to select ', '', $title);
      // echo $title;
      // echo $value['data-defaultasin'];v
      $asin = ($value->attr['data-defaultasin']);
  
      $data[] = [
        'title' => $title,
        'asin' => $asin
      ];
      // echo PHP_EOL;
      // echo "Sdf";
      
    }
    return $data;
  }

  function getInputByIdValue($doc, $id) {
    $asinNode = $doc->find("input[id=".$id."]");
    return count($asinNode)>0 ? trim($asinNode[0]->value) : null;
  }

  function getImages() {
	
    $list = $this->doc->find('#altImages ul li img');
    $data = [];
    foreach ($list as $key => $value) {
      $src = trim($value->src);
      if(strpos($src, '.gif')) continue;
      $src = str_replace('_SS40_', '_SL1000_', $src);
      $data[] = $src;
      
    }
    return $data;
  }
  

  function detail() {
      $doc = $this->doc;
      $title = trim($doc->find('#productTitle', 0)->plaintext);
      $brand = trim($doc->find('#bylineInfo', 0)->plaintext);
      $brandLink = trim($doc->find('#bylineInfo', 0)->href);
      $seller = trim($doc->find('#sellerProfileTriggerId', 0)->plaintext);
      $sellerLink = trim($doc->find('#sellerProfileTriggerId', 0)->href);
      $price = trim($doc->find('.priceblock_vat_inc_price', 0)->plaintext);
      $rating = trim($doc->find('#acrCustomerReviewText', 0)->plaintext);

      preg_match('~"marketplaceId":"(.*?)"~', $this->html, $marketplaceIdMatch );
      $marketplaceId = count($marketplaceIdMatch) > 1 ? $marketplaceIdMatch[1] : null;
      
      $descriptionNode = $doc->find('#productDescription p', 0);
      $description = $descriptionNode ? $descriptionNode->plaintext : null;

      $data = [
        'asin' => $this->getInputByIdValue($doc, 'ASIN'),
        'marketplaceId' => $marketplaceId,
        'isMerchantExclusive' => $this->getInputByIdValue($doc, 'isMerchantExclusive'),
        'merchantID' => $this->getInputByIdValue($doc, 'merchantID'),
        'offerListingID' => $this->getInputByIdValue($doc, 'offerListingID'),
        'productTitle' => $title,
        'brand' => $brand,
        'brandLink' => $brandLink,
        'seller' => $seller,
        'sellerLink' => $sellerLink,
        'price' => $price,
        'rating' => $rating,
        'description' => $description,
        'bulletPoints' => $this->bulletPoints(),
        'breadcrumbs' => $this->breadCrumb(),
        'variations' => $this->listVariations(),
        'images' => $this->getImages()
      ];

      return $data;
  }
}
?>