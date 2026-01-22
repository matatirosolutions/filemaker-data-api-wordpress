<?php

namespace FMDataAPI;

use \Exception;

/**
 * Class ShortCodeTable
 *
 * @package FMDataAPI
 */
class ShortCodeTable extends ShortCodeBase
{
    /**
     * ShortCodeTable constructor.
     *
     * @param FileMakerDataAPI $api
     * @param Settings $settings
     */
    public function __construct(FileMakerDataAPI $api, Settings $settings)
    {
        parent::__construct($api, $settings);

        add_shortcode('FM-DATA-TABLE', [$this, 'retrieveTableContent']);
    }

    /**
     * @param array $attr
     *
     * @return string
     */
    public function retrieveTableContent(array $attr)
    {
        if(!$this->validateAttributesOrExit(['layout', 'fields'], $attr)) {
            return '';
        }

        try {
            if(array_key_exists('query', $attr)) {
                return $this->performTableQuery($attr);
            }

			$limit = isset($attr['limit']) ? (int)$attr['limit'] : null;
			$records = $this->api->findAll($attr['layout'], $limit);

            return $this->generateTable($records, $attr);
        } catch (Exception $e) {
            return 'Unable to load records.';
        }
    }

    /**
     * @param array $attr
     *
     * @return string
     *
     * @throws Exception
     */
    private function performTableQuery(array $attr)
    {
        $query = $this->parseQueryToJSON($attr['query']);
        $sort = $this->generateSort($attr);
        $limit = isset($attr['limit']) ? (int)$attr['limit'] : 100;

        $records = $this->api->find($attr['layout'], $query, $sort, $limit);

        return $this->generateTable($records, $attr);
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    protected function parseQueryToJSON($queryString)
    {
        $reformattedQuery = html_entity_decode(
            str_replace("'", '"', $queryString)
        );

        return json_decode($reformattedQuery, true);
    }

    /**
     * @param array $records
     * @param array $attr
     *
     * @return string
     */
    private function generateTable(array $records, array $attr)
    {
        $fields = explode('|', $attr['fields']);
        $types = array_key_exists('types', $attr)
            ? explode('|', $attr['types'])
            : [];

        $html = '<table class="' . (isset($attr['class']) ? $attr['class'] : '') .  '">';
        $html .= array_key_exists('labels', $attr)
            ? $this->generateHeaderRow($attr['labels'])
            : $this->generateHeaderRow($attr['fields']);
        $html .= '<tbody>';


        foreach($records as $record) {
            $link = array_key_exists('id-field', $attr) && array_key_exists('detail-url', $attr)
                ? str_replace('*id*', $record[$attr['id-field']], $attr['detail-url'])
                : '';

                $html .= '<tr>';
            foreach($fields as $id => $field) {
                $type = array_key_exists($id, $types) ? $types[$id] : null;
                $html .= sprintf('<td>%s</td>', $this->outputField($record, trim($field), $type, $link));
            }
            $html .= '</tr>';
        }

        if(count($records) === 0) {
            $html .= '<tr><td colspan="' . count($fields) .'" style="text-align:center;">' . ($attr['message'] ?? 'No records found.') . '</td></tr>';

        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * @param $labels
     *
     * @return string
     */
    private function generateHeaderRow($labels)
    {
        $labels = explode('|', $labels);
        $html = '<thead><tr>';
        foreach($labels as $label) {
            $html .= trim(
                sprintf('<th>%s</th>', $label)
            );
        }
        $html .= '</tr></thead>';

        return $html;
    }

    private function generateSort(array $attr)
    {
        if(empty($attr['sort'])) {
            return [];
        }

        $reformattedQuery = html_entity_decode(
            str_replace("'", '"', $attr['sort'])
        );

        $options = json_decode($reformattedQuery, true);

        $sort = [];
        foreach($options as $field => $direction) {
            $sort[] = [
                'fieldName' => $field,
                'sortOrder' => $direction
            ];
        }

        return $sort;
    }
}
