<?php
namespace Phalcon2Rest\Models;

use Phalcon\Mvc\Model,
    Phalcon2Rest\Exceptions\HttpException,
    Phalcon\Mvc\Model\Validator\Uniqueness;

class Users extends Model {

    public $id;

    public $username;

    public $password;

    public $access;

    
    const ACCESS_USER = 1;
    const ACCESS_ADMIN = 2;

    /**
     * Returns the name of the table to use in the database
     * @return string
     */
    public function getSource() {
        return "users";
    }

    public function initialize()
    {
        $this->hasMany("id", "AuthorizedClients", "userId");
    }

    /**
     * Validates a model before submitting it for creation or deletion.  Our Princess model
     * must not be born before now, as we don't support future princesses.
     * @return bool
     * @throws HttpException    If the validation failed
     */
    public function validation() {
        $this->validate(new Uniqueness(array(
            "field"   => "username",
            "message" => "Value of field 'username' is already present in another record"
        )));
        if ($this->validationHasFailed() == true) {
            throw new HttpException(
                $this->appendMessage($this->getMessages()),
                417
            );
        }

        return true;
    }

}