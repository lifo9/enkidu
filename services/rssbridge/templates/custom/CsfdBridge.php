<?php
class CsfdBridge extends BridgeAbstract
{
    const MAINTAINER = 'No maintainer';
    const NAME = 'CSFD Verbal';
    const URI = 'https://www.csfd.cz/uzivatel/195357-verbal/recenze/';
    const DESCRIPTION = 'No description provided';

    public function collectData()
    {
        $page = getSimpleHTMLDOM($this->getURI());
        foreach ($page->find('#page-wrapper article') as $review) {
          $item = [];
          $rating = explode('-', $review->find(".star-rating .stars", 0)->class)[1];
          $timestamp = explode('.', $review->find(".header-right-info time", 0)->plaintext);
          $item['uri'] = 'https://csfd.cz' . $review->find(".film-title-name", 0)->href;
          $item['title'] = $review->find(".film-title-name", 0)->plaintext . ' ' . $review->find(".film-title-info span", 0)->plaintext . ' - ' . $rating . '/5';
          $item['timestamp'] = $timestamp[2].'-'.$timestamp[1].'-'.$timestamp[0];
          $item['author'] = 'verbal';
          $item['content'] = $review->find(".user-reviews-text p", 0)->plaintext;
          $item['enclosures'][] = 'https:' . $review->find(".article-img img", 0)->src;
          $item['uid'] = $item['title'] . ' - ' . $item['timestamp'];

          $this->items[] = $item;
        }
    }
}
