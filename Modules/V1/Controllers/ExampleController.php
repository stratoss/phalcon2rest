<?php
namespace Phalcon2Rest\Modules\V1\Controllers;

use Phalcon2Rest\Models\Books;

class ExampleController extends RestController {

    /**
     * Sets which fields may be searched against, and which fields are allowed to be returned in
     * partial responses.
     * @var array
     */
    protected $allowedFields = [
        'search' => ['id', 'author', 'title', 'year'],
        'partials' => ['id', 'author', 'title', 'year']
    ];

    public function get() {
        if ($this->isSearch) {
            $results = $this->search();
        } else {
            $results = Books::find()->toArray();
        }

        return $this->respond($results);
    }

    public function getOne($id) {
        return $this->respond(Books::findFirst($id)->toArray());
    }

    public function post() {
        return ['Post / stub'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete($id) {
        return ['Delete / stub'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function put($id) {
        return ['Put / stub'];
    }

    /**
     * @param int $id
     * @return array
     */
    public function patch($id) {
        return ['Patch / stub'];
    }

    public function search() {
        $results = [];
        $allBooks = Books::find()->toArray();
        foreach ($allBooks as $record) {
            $match = true;
            foreach ($this->searchFields as $field => $value) {
                if (strpos($record[$field], $value) === FALSE) {
                    $match = false;
                }
            }
            if ($match) {
                $results[] = $record;
            }
        }

        return $results;
    }

    public function respond($results) {
        if (count($results) > 0) {
            if ($this->isPartial) {
                $newResults = [];
                $remove = array_diff(array_keys($results[0]), $this->partialFields);
                foreach ($results as $record) {
                    $newResults[] = $this->array_remove_keys($record, $remove);
                }
                $results = $newResults;
            }
            if ($this->offset) {
                $results = array_slice($results, $this->offset);
            }
            if ($this->limit) {
                $results = array_slice($results, 0, $this->limit);
            }
        }
        return $results;
    }

    private function array_remove_keys($array, $keys = []) {

        // If array is empty or not an array at all, don't bother
        // doing anything else.
        if (empty($array) || (! is_array($array))) {
            return $array;
        }

        // At this point if $keys is not an array, we can't do anything with it.
        if (!is_array($keys)) {
            return $array;
        }

        // array_diff_key() expected an associative array.
        $assocKeys = array();
        foreach($keys as $key) {
            $assocKeys[$key] = true;
        }

        return array_diff_key($array, $assocKeys);
    }

}