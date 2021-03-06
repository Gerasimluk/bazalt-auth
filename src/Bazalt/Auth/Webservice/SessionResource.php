<?php

namespace Bazalt\Auth\Webservice;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Bazalt\Rest\Response;

/**
 * SessionResource
 *
 * @uri /auth/session
 */
class SessionResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getUser()
    {
        $user = \Bazalt\Auth::getUser();
        $ret = $user->toArray();

        return new Response(Response::OK, $ret);
    }

    /**
     * @method POST
     * @json
     */
    public function login()
    {
        /** @var \Bazalt\Auth\Model\User $user */
        $user = null;
        $data = Validator::create($this->request->data);
        $data->field('password')->required();
        $data->field('email')->required()->validator('exist_user', function($value) use (&$user, $data) {
            $user = User::getUserByLoginPassword($value, $data['password'], true);
            return ($user != null);
        }, 'User with this login/email does not exists');

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $user->login($data['remember_me'] == 'true');
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function logout()
    {
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return new Response(Response::OK, $user->toArray());
        }
        \Bazalt\Auth::logout();
        $user = \Bazalt\Auth::getUser();
        return new Response(Response::OK, $user->toArray());
    }
}