<?php

namespace Leantime\Plugins\LeanGroups\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Plugins\LeanGroups\Repositories\LeanGroupsRepository;
use Symfony\Component\HttpFoundation\Response;

class Pill extends Controller {

    private LeanGroupsRepository $repo;

    public function init(LeanGroupsRepository $repository): void {
        $this->repo = $repository;
    }

    public function post($params): Response{
        $ticketId = $params['ticket_id'];
        $groupId = $params['group_id'];
        $groupName = $params['group_name'];
        $this->repo->setTicketGroup($ticketId, $groupId);
        return new Response($groupName, 200, ['Content-Type' => 'text/html']);

    }


    public function showGroupPill($row): void
    {
        $row['group_id'] = $this->repo->getAssigmentGroup($row['id']);
        $groups = $this->repo->getGroups();
        //map groups to group ids
        $groupsMap = [];
        foreach ($groups as $group) {
            $groupsMap[$group['id']] = $group;
        }
        $payload = [
            'row' => $row,
            'groups' => $groupsMap
        ];
        echo view('LeanGroups::pill', $payload)->render();
    }


}
