<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 600);
ini_set('max_input_time', 600);
set_time_limit(0);
class PctuningBridge extends FeedExpander
{

  const MAINTAINER = 'No maintainer';
  const NAME = 'Pctuning bridge';
  const URI = '';
  const DESCRIPTION = 'No description provided';
  const PARAMETERS = array();
  const CACHE_TIMEOUT = 3600;

  public function collectData()
  {
    $this->collectExpandableDatas('https://pctuning.cz/rss/all', 10);
  }

  protected function parseItem($feedItem)
  {
    $item = parent::parseItem($feedItem);

    $host = 'https://' . parse_url($item['uri'])['host'];
    $first_page = getSimpleHTMLDOMCached($item['uri']);
    if ($first_page) {
      $author = $first_page->find('#app .post-header-info .post-header-info__name a', 0);
      $reklama = $first_page->find('#app .post-header-info .post-header-info__name span', 0);
      if ($reklama && $reklama->plaintext == 'Komerční sdělení') {
        return null;
      }
      if ($author) {
        if (!in_array($author->plaintext, ['Michal Rybka', 'Z. Obermaier', 'Pavel Tronner', 'Adam Vágner', 'Miroslav Ježek'])) {
          return null;
        }
        $item['author'] = $author->plaintext;
      }

      if (str_contains($first_page->find("#app .post-header__title", 0)->plaintext, 'Pokec o železe')) {
        return null;
      }

      $chapters = [];
      $chapters_outer = $first_page->find('#app .post .post-chapters__section', 0);
      if ($chapters_outer) {
        foreach ($chapters_outer->children() as $chapter) {
          $chapters[] = [$chapter->href, $chapter->plaintext];
        }
      }
      if ($chapters && is_array($chapters)) {
        array_shift($chapters);
      }
      $content = $this->extractContent($first_page);
      foreach ($chapters as $chapter) {
        $page = getSimpleHTMLDOMCached($host.$chapter[0]);
        $content .= $this->extractContent($page, $chapter[1]);
      }

      $item['content'] = $content;
    }

    $item['enclosures'] = [];

    return $item;
  }

  private function extractContent($page, $chapter_title = null)
  {
    $content = $page->find('#app .post', 0);
    $tags_to_remove = ['script', 'noscript', '.post-chapters--footer', '.post-footer', '.post-section--top-divider', '#article-header', '.post-body__sda', '.media-source', '.post-chapters', '.post-media', '.un-carousel__nav-button', '.un-carousel__description', '.un-carousel__zoom', '.un-carousel__bg', '.un-carousel__pagination', ''];
    if ($chapter_title) {
      $tags_to_remove[] = '.post-body__perex';
    }

    if (!$content) {
      return '';
    }

    foreach ($tags_to_remove as $tag_name) {
      foreach ($content->find($tag_name) as $tag) {
        if (is_array($tag)) {
          foreach ($tag as $tag_element) {
            $tag_element->remove();
          }
        } else {
          $tag->remove();
        }
      }
    }

    return $chapter_title ? "<br><h2>$chapter_title</h2><br>" . $content : $content;
  }
}
