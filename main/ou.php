<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\OU;
use App\Main\User;
use App\Main\Group;
use App\Main\Claimant;

if (!$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!");
}

switch ((string)$request->get('f')) {
    case 'save':
        $arOu = $request->request->get('ou');
        $arOu['is_tutors'] = (int)$arOu['is_tutors'];
        $ou = $app->mm->ou((int)$arOu['id'], $arOu);
        $app->em->flush();
        $result = array(
            'id' => $ou->getId(),
            'code' => $ou->getCode(),
            'description' => $ou->getDescription(),
            'sort' => $ou->getSort(),
            'is_tutors' => $ou->getIsTutors()? 1: 0,
            'user_id' => $ou->getUser()? $ou->getUser()->getId(): 0,
            'tutor' => $ou->getUser()? sprintf('%s (%s)', $ou->getUser()->getLogin(), $ou->getUser()->getAlias()): '',
        );
        break;
    case 'remove':
        $ou = $app->mm->ou((int)$request->request->get('id'));
        $app->em->remove($ou);
        $app->em->flush();
        $result = array(
            'id' => (int)$request->request->get('id')
        );
        break;
    case 'list':
        $query = $app->em->createQuery('SELECT ou FROM '.OU::class.' ou ORDER BY ou.sort ASC, ou.code ASC');
        $result = array();
        foreach ($query->execute() as $ou) {
            $result[] = array(
                'id' => $ou->getId(),
                'code' => $ou->getCode(),
                'description' => $ou->getDescription(),
                'sort' => $ou->getSort(),
                'is_tutors' => $ou->getIsTutors()? 1: 0,
                'user_id' => $ou->getUser()? $ou->getUser()->getId(): 0,
                'tutor' => $ou->getUser()? sprintf('%s (%s)', $ou->getUser()->getLogin(), $ou->getUser()->getAlias()): '',
            );
        }
        break;
    case 'load-tutor':
        $dql = 'SELECT u FROM '.User::class.' u JOIN u.ou ou WHERE ou.isTutors = 1 ORDER BY u.login ASC';
        $query = $app->em->createQuery($dql);
        $result = array();
        foreach ($query->execute() as $user) {
            $result[] = array(
                'value' => $user->getId(),
                'text' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias())
            );
        }
        break;
}

$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>