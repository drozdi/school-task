<?php require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

//$result->setStatusCode(403, 'asdbaksdbsajd');

    switch ($request->get('f', null)) {
        case 'check-is-authenticated':
            if ($app->user->isAuthorized()) {
                $user = $app->user->getUser();
                $response = new JsonResponse(array(
                    'id' => $user->getId(),
                    'parent_id' => $user->getParent() ? $user->getParent()->getId() : null,
                    'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
                    'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
                    'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
                    'last_ip' => $user->getLastIp(),
                    'active' => $user->getActive() ? 1 : 0,
                    'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
                    'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
                    'loocked ' => $user->getLoocked() ? 1 : 0,
                    'stored_hash' => $user->getStoredHash(),
                    'checkword' => $user->getCheckword(),
                    'login' => $user->getLogin(),
                    'email' => $user->getEmail(),
                    'password' => $user->getPassword(),
                    'alias' => $user->getAlias(),
                    'first_name' => $user->getFirstName(),
                    'second_name' => $user->getSecondName(),
                    'patronymic' => $user->getPatronymic(),
                    'gender' => $user->getGender(),
                    'login_attempts' => $user->getLoginAttempts(),
                    'country' => $user->getCountry(),
                    'ou_id' => $user->getOu() ? $user->getOu()->getId() : null,
                    'phone' => $user->getPhone(),
                    'description' => $user->getDescription(),
                    'is_root' => $app->user->checkAccess('root', 7),
                    'is_class' => $app->user->checkAccess('class', 4)
                ), Response::HTTP_CREATED);
                $response->headers->set('Content-Type', 'application/json');
                $response->send();
            } else {
                $response = new JsonResponse(false);
                $response->send();
            }
            break;
        case 'logout':
            $app->user->logout();
            $response = new JsonResponse();
            $response->headers->set('Location', '/');
            $response->send();
            break;
        case 'login':
            $q = (array)$request->request->get('user', array());
            if ($app->user->login($q['login'], $q['password'])) {
                $user = $app->user->getUser();
                $response = new JsonResponse(array(
                    'id' => $user->getId(),
                    'parent_id' => $user->getParent() ? $user->getParent()->getId() : null,
                    'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
                    'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
                    'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
                    'last_ip' => $user->getLastIp(),
                    'active' => $user->getActive() ? 1 : 0,
                    'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
                    'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
                    'loocked ' => $user->getLoocked() ? 1 : 0,
                    'stored_hash' => $user->getStoredHash(),
                    'checkword' => $user->getCheckword(),
                    'login' => $user->getLogin(),
                    'email' => $user->getEmail(),
                    'password' => $user->getPassword(),
                    'alias' => $user->getAlias(),
                    'first_name' => $user->getFirstName(),
                    'second_name' => $user->getSecondName(),
                    'patronymic' => $user->getPatronymic(),
                    'gender' => $user->getGender(),
                    'login_attempts' => $user->getLoginAttempts(),
                    'country' => $user->getCountry(),
                    'ou_id' => $user->getOu() ? $user->getOu()->getId() : null,
                    'phone' => $user->getPhone(),
                    'description' => $user->getDescription(),
                    'is_root' => $app->user->checkAccess('root', 7),
                    'is_class' => $app->user->checkAccess('class', 7)
                ), Response::HTTP_CREATED);
                $response->headers->set('Content-Type', 'application/json');
                $response->send();

            } else {
                $response = new JsonResponse(array(
                    'error' => "Неверный Логин/Пароль!"
                ));
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED, "Неверный Логин/Пароль!");
                $response->headers->set('Content-Type', 'application/json');
                //$response->headers->set('Location', '/login');
                $response->send();
            }
            break;
    }


    /*if ('load-acc' === $request->get('f', null)) {
        if ($app->user->isAuthorized()) {
            $user = $app->user->getUser();
            $response = new JsonResponse(array(
                'id' => $user->getId(),
                'parent_id' => $user->getParent() ? $user->getParent()->getId() : null,
                'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
                'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
                'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
                'last_ip' => $user->getLastIp(),
                'active' => $user->getActive() ? 1 : 0,
                'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
                'loocked ' => $user->getLoocked() ? 1 : 0,
                'stored_hash' => $user->getStoredHash(),
                'checkword' => $user->getCheckword(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'alias' => $user->getAlias(),
                'first_name' => $user->getFirstName(),
                'second_name' => $user->getSecondName(),
                'patronymic' => $user->getPatronymic(),
                'gender' => $user->getGender(),
                'login_attempts' => $user->getLoginAttempts(),
                'country' => $user->getCountry(),
                'ou_id' => $user->getOu() ? $user->getOu()->getId() : null,
                'phone' => $user->getPhone(),
                'description' => $user->getDescription()
            ), Response::HTTP_CREATED);
            $response->headers->set('Content-Type', 'application/json');
            $response->send();
        } else {
            $response = new RedirectResponse('/login', Response::HTTP_UNAUTHORIZED);
            $response->send();
        }
    } elseif (null != $request->get('logout', null)) {
        $app->user->logout();
        $response = new JsonResponse();
        $response->headers->set('Location', '/');
        $response->send();
    } else {
        $q = (array)$request->request->get('user', array());
        if ($app->user->login($q['login'], $q['password'])) {
            $user = $app->user->getUser();
            $response = new JsonResponse(array(
                'id' => $user->getId(),
                'parent_id' => $user->getParent() ? $user->getParent()->getId() : null,
                'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
                'date_register ' => $user->getDateRegister("Y-m-d H:m:s"),
                'last_login ' => $user->getLastLogin("Y-m-d H:m:s"),
                'last_ip' => $user->getLastIp(),
                'active' => $user->getActive() ? 1 : 0,
                'activeFrom' => $user->getActiveFrom("Y-m-d H:m:s"),
                'activeTo' => $user->getActiveTo("Y-m-d H:m:s"),
                'loocked ' => $user->getLoocked() ? 1 : 0,
                'stored_hash' => $user->getStoredHash(),
                'checkword' => $user->getCheckword(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'alias' => $user->getAlias(),
                'first_name' => $user->getFirstName(),
                'second_name' => $user->getSecondName(),
                'patronymic' => $user->getPatronymic(),
                'gender' => $user->getGender(),
                'login_attempts' => $user->getLoginAttempts(),
                'country' => $user->getCountry(),
                'ou_id' => $user->getOu() ? $user->getOu()->getId() : null,
                'phone' => $user->getPhone(),
                'description' => $user->getDescription()
            ), Response::HTTP_CREATED);
            $response->headers->set('Content-Type', 'application/json');
            $response->send();

            $response = new RedirectResponse('/', Response::HTTP_CREATED);
            $response->send();
        } else {
            $response = new JsonResponse(array(
                'error' => "Неверный Логин/Пароль!"
            ));
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED, "Неверный Логин/Пароль!");
            $response->headers->set('Content-Type', 'application/json');
            //$response->headers->set('Location', '/login');
            $response->send();
        }
    }*/
