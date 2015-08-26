<?php
namespace Bread\Satisfaction;

use Bread\Configuration\Manager as Configuration;
use Bread\REST;

class Model extends REST\Model
{

    const VOTE_LOW = '1';
    const VOTE_MEDIUM_LOW = '3';
    const VOTE_MEDIUM_HIGH = '5';
    const VOTE_HIGH = '7';

    protected $created;

    protected $vote;

    protected $text;

    protected $mail;

    protected $firstName;

    protected $lastName;

    protected $birthDate;

}

Configuration::defaults('Bread\Satisfaction\Model', array(
    'properties' => array(
        'created' => array(
            'type' => 'Bread\Types\DateTime'
        ),
        'vote' => array(
            'type' => 'integer'
        ),
        'birthdate' => array(
            'type' => 'Bread\Types\DateTime'
        )
    )
));
