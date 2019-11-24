<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for Turbo RSS feed templates.
 *
 * Default template: views-view-turbo-rss.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - view: A ViewExecutable object.
 *   - rows: The raw row data.
 */
function custom_yandex_turbo_preprocess_views_view_turbo_rss(&$variables)
{
    global $base_url;

    $view = $variables['view'];
    $items = $variables['rows'];
    $style = $view->style_plugin;
    $path = $view->display_handler->getPath();

    $variables['link'] = $base_url . '/' . $path;
    $variables['title'] = $view->getTitle();
    $variables['description'] = $style->getDescription();
    $variables['langcode'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $variables['namespaces'] = new Attribute($style->namespaces);
    $variables['items'] = $items;

    if (empty($variables['view']->live_preview)) {
        $variables['view']->getResponse()->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}

/**
 * Prepares variables for views Turbo RSS item templates.
 *
 * Default template: views-view-row-turbo-rss.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - row: The raw results rows.
 */
function custom_yandex_turbo_preprocess_views_view_row_turbo_rss(&$variables)
{
    if ( !empty($variables['row']) ){
      $item = $variables['row'];
      $variables['title'] = isset($item->title) ? $item->title : '';
      $variables['link'] = isset($item->link) ? $item->link : '';
      $variables['pubDate'] = isset($item->pubDate) ? $item->pubDate : '';
      $variables['author'] = isset($item->author) ? $item->author : '';
      $variables['content'] = isset($item->content) ? (string)\Drupal::service('renderer')->render($item->content) : '';
    }
}
