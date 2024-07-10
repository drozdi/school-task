<?php
require_once "../x/.init.php";
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\EpGroup;
$res = array();
$query = $app->em->createQuery("SELECT g FROM ".EpGroup::class." g JOIN g.parent p WHERE p.code LIKE 'class_%' AND p.parent IS NULL ORDER BY p.sort ASC, g.sort ASC, p.name ASC");
$result = array();
foreach ($query->execute() as $group) {
    $res[] = array(
        'icon' => "",
        'title' => "Класс ".$group->getName(),
        'href' => "/event/".$group->getId()
    );
}
$res = new JsonResponse($res);
$res->headers->set('Content-Type', 'application/json');
$res->send();