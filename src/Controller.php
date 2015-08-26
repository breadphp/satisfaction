<?php
namespace Bread\Satisfaction;

use Bread\Promises\When;
use Bread\REST;
use Bread\Types\DateTime;

class Controller extends REST\Controller
{
    public function get($resource, array $parameters = array())
    {
        // TODO authorize
        return parent::get($resource);
    }

    public function getVotes($resource)
    {
        $search = (array) $this->request->query['search'];
        if (isset($search['created-min']) && isset($search['created-max'])) {
            $search = array(
                '$and' => array(
                    array(
                        'created' => array('$gte' => new DateTime($search['created-min']))
                    ),
                    array(
                        'created' => array('$lte' => new DateTime($search['created-max']))
                    )
                )
            );
        }
        $promises = array(
            'low' => Model::count(array_merge($search, array('vote' => Model::VOTE_LOW)), array(), $this->domain),
            'medium_low' => Model::count(array_merge($search, array('vote' => Model::VOTE_MEDIUM_LOW)), array(), $this->domain),
            'medium_high' => Model::count(array_merge($search, array('vote' => Model::VOTE_MEDIUM_HIGH)), array(), $this->domain),
            'high' => Model::count(array_merge($search, array('vote' => Model::VOTE_HIGH)), array(), $this->domain)
        );
        return When::all($promises, function($counts) {
            return parent::get($counts);
        });
    }

    public function post($resource)
    {
        switch ($this->request->type)  {
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
                return $this->data()->then(function ($data) {
                    $model = new Model(array(
                        'created' => new DateTime()
                    ));
                    foreach ($data as $field => $value) {
                        switch ($field) {
                            case 'vote':
                                $model->$field = (int) trim(stream_get_contents(array_shift($value)['body']));
                                break;
                            case 'birthdate':
                                $model->$field = new DateTime(trim(stream_get_contents(array_shift($value)['body'])));
                                break;
                            default:
                                $model->$field = trim(stream_get_contents(array_shift($value)['body']));
                                break;
                        }
                    }
                    return $model->store($this->domain);
                });
            case 'application/json':
                return $this->data()->then(function ($json) {
                    $model = Model::fromJSON($json);
                    return $model->store($this->domain);
                })->then(function($resource) {
                    return $this->storeACL($resource);
                });
            default:
                throw new UnsupportedMediaType($this->request->type);
        }
    }

    public function controlledResource() {
        if ($this->request->method === 'POST' || $this->request->method === 'OPTIONS' || $this->request->uri === '/votes') {
            return When::resolve(array());
        }
        $search = (array) $this->request->query['search'];
        $options = (array) $this->request->query['options'];
        if (isset($search['created-min']) && isset($search['created-max'])) {
            $search = array(
                '$and' => array(
                    array(
                        'created' => array('$gte' => new DateTime($search['created-min']))
                    ),
                    array(
                        'created' => array('$lte' => new DateTime($search['created-max']))
                    )
                )
            );
        }
        $this->response->headers['X-Count'] = Model::count($search, array(), $this->domain);
        return Model::fetch($search, $options, $this->domain);
    }

    protected function authorize($resource)
    {

    }
}