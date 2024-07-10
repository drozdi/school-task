<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/x/.init.php';
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Main\OU;
use App\Main\User;
use App\Main\Claimant;
use App\Main\Group;

if (!$app->user->checkAccess('root', 7)) {
    throw new Exception("Недостаточно прав!", 403);
}

switch ((string)$request->get('f')) {
    case 'save':
        $arClaimant = $request->request->get('claimant');
        $claimant = $app->mm->claimant(!empty($arClaimant['id'])?(int)$arClaimant['id']: null, $arClaimant);
        $app->em->flush();
        $result = array(
            'id' => $claimant->getId(),
            'code' => $claimant->getCode(),
            'name' => $claimant->getName(),
        );
        break;
    case 'remove':
        $claimant = $app->mm->claimant((int)$request->request->get('id'));
        $app->em->remove($claimant);
        $app->em->flush();
        $result = array(
            'id' => (int)$request->request->get('id')
        );
        break;
    case 'list':
        $query = $app->em->createQuery('SELECT c FROM '.Claimant::class.' c ORDER BY c.code ASC');
        $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        $result = array();
        foreach ($query->execute() as $c) {
            $result[] = $c;
        }
        break;
}

$result = new JsonResponse($result?: array());
$result->headers->set('Content-Type', 'application/json');
$result->send();
?>