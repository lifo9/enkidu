<?php
class DennikNBridge extends FeedExpander
{

  const MAINTAINER = 'No maintainer';
  const NAME = 'Dennik N';
  const URI = '';
  const DESCRIPTION = 'No description provided';
  const PARAMETERS = array();
  const CACHE_TIMEOUT = 3600;

  public function collectData()
  {
    $this->collectExpandableDatas('https://dennikn.sk/feed');
  }

  protected function parseItem($feedItem)
  {
    $item = parent::parseItem($feedItem);
    $article = getSimpleHTMLDOMCached($item['uri'], $this->CACHE_TIMEOUT, ['Cookie: ' . getenv("DENNIKN_AUTH_COOKIE")]);
    $head = $article->find('article .b_single_e', 0);
    $content = $article->find('article .a_single__post', 0);

    if (
      $article &&
      ($head || $content) &&
      $item['author'] != 'Shooty' && // Shooty is in a separate feed
      strpos($article->find('#schema', 0)->innertext, 'sport') === false // filter out sport articles
    ) {
      $item = $this->addArticleToItem($item, $head, $content);
      return $item;
    }
  }

  private function addArticleToItem($item, $head, $content)
  {
    $tags_to_remove = ['script', 'noscript', 'iframe', '.iframely-embed', '.t_embed'];

    if ($content !== null) {
      foreach ($tags_to_remove as $tag_name) {
        foreach ($content->find($tag_name) as $tag) {
          $tag->remove();
        }
      }
    }

    if ($head !== null) {
      foreach ($tags_to_remove as $tag_name) {
        foreach ($head->find($tag_name) as $tag) {
          $tag->remove();
        }
      }
    }

    $item['content'] = $head;
    $item['content'] .= $content;

    return $item;
  }
}
