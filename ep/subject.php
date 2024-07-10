<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\User;
use App\Entity\EpSubject;

if (!$app->user->checkAccess('subject', 7) && !$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!");
}

switch ((string)$request->get('f')) {
    case 'save':
        $arSubject = $request->request->get('subject');
        $arSubject['users'] = (array)(!empty($arSubject['users'])?$arSubject['users']:array());
        $subject = $app->ep->subject(!empty($arSubject['id'])?(int)$arSubject['id']:null, $arSubject);
        $app->em->flush();
        $users = array();
        foreach ($subject->getUsers() as $user) {
            $users[$user->getId()] = array(
                'user_id' => $user->getId(),
                'name' => sprintf('%s (%s)', $user->getLogin(), $user->getAlias())
            );
        }
        $result = array(
            'id' => $subject->getId(),
            'name' => $subject->getName(),
            'sort' => $subject->getSort(),
        );
        if (!empty($users)) {
            $result['users'] = $users;
        }
        break;
    case 'remove':
        $claimant = $app->ep->subject((int)$request->request->get('id'));
        $app->em->remove($claimant);
        $app->em->flush();
        $result = array(
            'id' => (int)$request->request->get('id')
        );
        break;
    case 'list':
        $query = $app->em->createQuery('SELECT s FROM '.EpSubject::class.' s ORDER BY s.sort ASC, s.name ASC');
        $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $result = array();
        foreach ($query->execute() as $c) {
            $result[] = $c;
        }
        break;
    case 'load':
        $subject = $app->ep->subject((array)$request->request->get('subject', array()));
        $users = array();
        foreach ($subject->getUsers() as $user) {
            $users[$user->getId()] = array(
                'user_id' => $user->getId(),
                'name' => sprintf('%s (%s)', $user->getAlias(), $user->getLogin())
            );
        }
        $result = array(
            'id' => $subject->getId(),
            'name' => $subject->getName(),
            'sort' => $subject->getSort()
        );
        if (!empty($users)) {
            $result['users'] = $users;
        }
        break;
    case 'load-teachers':
        $result = array();
        $dql = "SELECT ug FROM ".User\Group::class." ug JOIN ug.group g WHERE g.code = 'teachers'";
            //' ORDER BY u.login ASC';
        foreach ($app->em->createQuery($dql)->execute() as $ug) {
            $result[] = array(
                'value' => $ug->getUserId(),
                'text' => sprintf('%s (%s)', $ug->getUserAlias(), $ug->getUserLogin()),
            );
        }
        break;
}
$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>