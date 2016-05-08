<?php
namespace Phalcon2Rest\Models;

use Phalcon\Mvc\Model;

class Clients extends Model {

    /**
     * Returns the name of the table to use in the database
     * @return string
     */
    public function getSource() {
        return "clients";
    }
}