<?php
require_once '../x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\OU;
use App\Main\User;
use App\Main\Group;
use App\Main\Claimant;

switch ((string)$request->get('f')) {
    case 'load':
        $user = $app->user->getUser();
        $result = array(
            'id' => $user->getId(),
            'tutor' => $user->getParent()? sprintf('%s (%s)', $user->getParent()->getAlias(), $user->getParent()->getLogin()) : "",
            'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
            'date_register' => $user->getDateRegister("Y-m-d H:m:s"),
            'last_login' => $user->getLastLogin()->format("d.m.Y H:i"),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'alias' => $user->getAlias(),
            'first_name' => $user->getFirstName(),
            'second_name' => $user->getSecondName(),
            'patronymic' => $user->getPatronymic(),
        );
        break;
    case 'save':
        $arUser = (array)$request->request->get('user', array());
        $user = $app->mm->user((int)$arUser['id'], $arUser);
        $result = array(
            'id' => $user->getId(),
            'tutor' => $user->getParent()? sprintf('%s (%s)', $user->getParent()->getAlias(), $user->getParent()->getLogin()) : "",
            'x_timestamp' => $user->getXTimestamp("Y-m-d H:m:s"),
            'date_register' => $user->getDateRegister("Y-m-d H:m:s"),
            'last_login' => $user->getLastLogin()->format("d.m.Y H:i"),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'alias' => $user->getAlias(),
            'first_name' => $user->getFirstName(),
            'second_name' => $user->getSecondName(),
            'patronymic' => $user->getPatronymic(),
        );
        $app->em->flush();
        break;
}

$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>