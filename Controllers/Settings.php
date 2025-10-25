<?php

namespace Leantime\Plugins\LeanGroups\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Plugins\LeanGroups\Repositories\LeanGroupsRepository;
use Symfony\Component\HttpFoundation\Response;

class Settings extends Controller
{
    private LeanGroupsRepository $repo;
    private Clients $clientsRepo;

    public function init(LeanGroupsRepository $repository,Clients $clientsRepo): void {
        $this->repo = $repository;
        $this->clientsRepo = $clientsRepo;
    }

    public function get(): Response{
        $groups = $this->repo->getGroups();
        $this->tpl->assign('groups', $groups);
        $clients = $this->clientsRepo->getAll();
        $this->tpl->assign('clients', $clients);
        return $this->tpl->display('LeanGroups.settings');
    }

    public function post(array $params): Response {
        // Create a new group
        $req = $this->incomingRequest->request;
        $name = trim((string) $req->get('name', ''));
        $description = $req->get('description', null);
        $client = $req->get('client_id', null);
        $clientId = $client !== '' ? (int) $client : null;
        if ($name !== '') {
            $this->repo->createGroup($name, $description, $clientId);
        }
        return Frontcontroller::redirect(BASE_URL.'/LeanGroups/settings');
    }

    public function delete(): Response {
        $groupId = isset($_REQUEST['group_id']) ? (int)$_REQUEST['group_id'] : 0;
        if ($groupId > 0) {
            $this->repo->deleteGroup($groupId);
        }
        return Frontcontroller::redirect(BASE_URL.'/LeanGroups/settings');
    }
}
