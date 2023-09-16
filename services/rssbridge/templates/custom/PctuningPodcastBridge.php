<?php
class PctuningPodcastBridge extends FeedExpander
{

  const MAINTAINER = 'No maintainer';
  const NAME = 'Pctuning podcast bridge';
  const URI = '';
  const DESCRIPTION = 'No description provided';
  const PARAMETERS = array();
  const CACHE_TIMEOUT = 3600;


  public function collectData()
  {
    $this->collectExpandableDatas('https://pctuning.cz/rss/podcasty');
  }

  protected function parseItem($feedItem)
  {
    $item = parent::parseItem($feedItem);
    $item['enclosures'] = [(string)$feedItem->xpath('enclosure')[1]['url']];

    return $item;
  }
}
