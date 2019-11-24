<?php

namespace Drupal\custom_yandex_turbo\Plugin\views\row;

use Drupal\views\Plugin\views\row\RowPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Renders an Turbo RSS item based on fields.
 *
 * @ViewsRow(
 *   id = "turbo_rss_fields",
 *   title = @Translation("Turbo Fields"),
 *   help = @Translation("Display fields as Turbo RSS items."),
 *   theme = "views_view_row_turbo_rss",
 *   display_types = {"feed"}
 * )
 */
class TurboRssFields extends RowPluginBase {

  /**
   * Does the row plugin support to add fields to its output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['title_field'] = ['default' => ''];
    $options['link_field'] = ['default' => ''];
    $options['content_field'] = ['default' => ''];
    $options['author_field'] = ['default' => ''];
    $options['date_field'] = ['default' => ''];
    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $initial_labels = ['' => $this->t('- None -')];
    $view_fields_labels = $this->displayHandler->getFieldLabels();
    $view_fields_labels = array_merge($initial_labels, $view_fields_labels);

    $form['title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Title field'),
      '#description' => $this->t('The field that is going to be used as the RSS item title for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['title_field'],
      '#required' => TRUE,
    ];
    $form['link_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Link field'),
      '#description' => $this->t('The field that is going to be used as the RSS item link for each row. This must be a drupal relative path.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['link_field'],
      '#required' => TRUE,
    ];
    $form['content_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Content field'),
      '#description' => $this->t('The field that is going to be used as the RSS item content for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['content_field'],
      '#required' => TRUE,
    ];
    $form['author_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Author field'),
      '#description' => $this->t('The field that is going to be used as the RSS item creator for each row.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['author_field'],
      '#required' => TRUE,
    ];
    $form['date_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Publication date field'),
      '#description' => $this->t('The field that is going to be used as the RSS item pubDate for each row. It needs to be in RFC 2822 format.'),
      '#options' => $view_fields_labels,
      '#default_value' => $this->options['date_field'],
      '#required' => TRUE,
    ];

  }

  public function validate() {
    $errors = parent::validate();
    $required_options = ['title_field', 'link_field', 'content_field', 'author_field', 'date_field'];
    foreach ($required_options as $required_option) {
      if (empty($this->options[$required_option])) {
        $errors[] = $this->t('Row style plugin requires specifying which views fields to use for RSS item.');
        break;
      }
    }
    return $errors;
  }

  public function render($row) {
    static $row_index;
    if (!isset($row_index)) {
      $row_index = 0;
    }

    // Create the RSS item object.
    $item = new \stdClass();
    $item->title = $this->getField($row_index, $this->options['title_field']);
    $item->link = Url::fromUserInput('/' . $this->getField($row_index, $this->options['link_field']))->setAbsolute()->toString();

    $content = (string) $this->getField($row_index, $this->options['content_field']);

    $item->content = is_array($content) ? $content : ['#markup' => $content];
    $item->pubDate = $this->getField($row_index, $this->options['date_field']);
    $item->author =  $this->getField($row_index, $this->options['author_field']);

    $row_index++;

    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#row' => $item,
      '#field_alias' => isset($this->field_alias) ? $this->field_alias : '',
    ];

    return $build;
  }

  /**
   * Retrieves a views field value from the style plugin.
   *
   * @param $index
   *   The index count of the row as expected by views_plugin_style::getField().
   * @param $field_id
   *   The ID assigned to the required field in the display.
   *
   * @return string|null|\Drupal\Component\Render\MarkupInterface
   *   An empty string if there is no style plugin, or the field ID is empty.
   *   NULL if the field value is empty. If neither of these conditions apply,
   *   a MarkupInterface object containing the rendered field value.
   */
  public function getField($index, $field_id) {
    if (empty($this->view->style_plugin) || !is_object($this->view->style_plugin) || empty($field_id)) {
      return '';
    }
    return $this->view->style_plugin->getField($index, $field_id);
  }

}
