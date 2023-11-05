<?php
class InvidiousPlaylistBridge extends FeedExpander
{

  const MAINTAINER = 'No maintainer';
  const NAME = 'Invidious Playlist bridge';
  const URI = '';
  const DESCRIPTION = 'No description provided';
  const CACHE_TIMEOUT = 3600;

  const PARAMETERS = [[
    'invidious_host' => [
      'name' => 'Invidious Host URL',
      'required' => true
    ],
    'playlist_id' => [
      'name' => 'Invidious Playlist ID',
      'required' => true
    ]
  ]];

  public function collectData()
  {
    $videos = [];
    $page = 1;

    while (true) {
      $playlist_url = $this->getInput('invidious_host') . '/api/v1/playlists/' . $this->getInput('playlist_id') . '?page=' . $page++;
      $playlist = json_decode(getContents($playlist_url), true);
      $current_page_videos = $playlist['videos'];
      if (count($current_page_videos) == 0) {
        break;
      }
      $videos = array_merge($videos, $current_page_videos);
    }

    $videos = array_reverse($videos); // Newest first

    foreach ($videos as $video) {
      $this->items[] = $this->parseItem($video);
    }
  }

  protected function parseItem($feedItem)
  {
    $item = [];
    $item['uri'] = "https://youtube.com/watch?v=" . $feedItem['videoId'];
    $item['content'] = '';

    return $item;
  }
}
