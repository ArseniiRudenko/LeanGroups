<?php

namespace Leantime\Plugins\LeanGroups\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Domain\Projects\Repositories\Projects;
use Leantime\Plugins\LeanGroups\Repositories\LeanGroupsRepository;
use Symfony\Component\HttpFoundation\Response;

class GroupMembership extends Controller
{
    private LeanGroupsRepository $repo;
    private Clients $clientsRepo;
    private Projects $projectsRepo;

    public function init(LeanGroupsRepository $repository, Clients $clientsRepo, Projects $projectsRepo): void {
        $this->repo = $repository;
        $this->clientsRepo = $clientsRepo;
        $this->projectsRepo = $projectsRepo;
    }

    public function get(): Response{
        $req = $this->incomingRequest->query;
        $groupId = (int) $req->get('group_id', 0);
        if ($groupId <= 0) {
            return Frontcontroller::redirect(BASE_URL.'/LeanGroups/settings');

        }
        $group = $this->repo->getGroup($groupId);
        $members = $this->repo->getGroupMembers($groupId);
        $projects = $this->repo->getGroupProjects($groupId);
        // Additional data for dropdowns
        $clients = $this->clientsRepo->getAll();
        $allProjects = $this->projectsRepo->getProjectNames();
        $allUsers = $this->repo->getAllUsersNotInGroup($groupId);
        $this->tpl->assign('group', $group);
        $this->tpl->assign('members', $members);
        $this->tpl->assign('projects', $projects);
        $this->tpl->assign('clients', $clients);
        $this->tpl->assign('allProjects', $allProjects);
        $this->tpl->assign('allUsers', $allUsers);
        $this->tpl->assign('roles', Roles::getRoles());
        return $this->tpl->display('LeanGroups.groupMembership');
    }

    public function post(array $params): Response{
        $req = $this->incomingRequest->request;

        $groupId = (int) $req->get('group_id', 0);
        $action = (string) $req->get('action', '');
        if ($groupId <= 0) { return Frontcontroller::redirect(BASE_URL.'/LeanGroups/settings');  }
        switch ($action) {
            case 'add_member':
                $userId = (int) $req->get('user_id', 0);
                if ($userId > 0) { $this->repo->addMember($groupId, $userId); }
                break;
            case 'remove_member':
                $userId = (int) $req->get('user_id', 0);
                if ($userId > 0) { $this->repo->removeMember($groupId, $userId); }
                break;
            case 'add_project':
                $projectId = (int) $req->get('project_id', 0);
                if ($projectId > 0) {
                    $this->repo->addProjectToGroup($groupId, $projectId);
                    $roleKeyRaw = $req->get('role_key', '');
                    if ($roleKeyRaw !== '') {
                        $roleKey = (int)$roleKeyRaw;
                        $rolesMap = Roles::getRoles();
                        if (array_key_exists($roleKey, $rolesMap)) {
                            $this->repo->setGroupRoleForProject($groupId, $projectId, $roleKey);
                        }
                    }
                }
                break;
            case 'remove_project':
                $projectId = (int) $req->get('project_id', 0);
                if ($projectId > 0) { $this->repo->removeProjectFromGroup($groupId, $projectId); }
                break;
            case 'update_group':
                $data = [];
                if ($req->has('name')) { $data['name'] = trim((string) $req->get('name')); }
                if ($req->has('description')) { $data['description'] = $req->get('description'); }
                if ($req->has('color')) { $data['color'] = $req->get('color'); }
                if ($req->has('client_id')) {
                    $clientId = $req->get('client_id');
                    $data['client_id'] = $clientId !== '' ? (int) $clientId : null;
                }
                if ($data) { $this->repo->updateGroup($groupId, $data); }
                break;
            case 'set_role':
                $projectId = (int) $req->get('project_id', 0);
                $roleKeyRaw = $req->get('role_key', '');
                if ($projectId > 0) {
                    if ($roleKeyRaw === '') {
                        $this->repo->setGroupRoleForProject($groupId, $projectId, null);
                    } else {
                        $roleKey = (int)$roleKeyRaw;
                        $rolesMap = Roles::getRoles();
                        if (array_key_exists($roleKey, $rolesMap)) {
                            $this->repo->setGroupRoleForProject($groupId, $projectId, $roleKey);
                        }
                    }
                }
                break;
        }
        return Frontcontroller::redirect(BASE_URL.'/LeanGroups/groupMembership?group_id='.$groupId);
    }


    public function showGroupInTicket($payload)
    {
        $ticket = $payload['ticket'];
        $payload['ticketGroup'] = $this->repo->getAssigmentGroup($ticket->id);
        $payload['groups'] = $this->repo->getGroups();
        echo view('LeanGroups::assigmentGroup', $payload)->render();
    }

}
