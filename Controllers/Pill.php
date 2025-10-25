<?php

namespace Leantime\Plugins\LeanGroups\Controllers;

use Illuminate\Support\Facades\Cache;
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


    public function showGroupPillTicket($row): void
    {
        $row['group_id'] = $this->repo->getAssigmentGroup($row['id']);
        $groupsMap = $this->getGroupsMap();
        $payload = [
            'row' => $row,
            'groups' => $groupsMap
        ];
        echo view('LeanGroups::pill', $payload)->render();
    }

    public function showTableHeader($void):void
    {
        echo "<th class='group-col'>".$this->tpl->__('label.group')."</th>";
    }

    private function getGroupsMap()
    {
        return Cache::remember('lean_groups_map', 60, function () {
            $groups = $this->repo->getGroups();
            $groupsMap = [];
            foreach ($groups as $group) {
                $groupsMap[$group['id']] = $group;
            }
            return $groupsMap;
        });
    }


    public function showTableRow($payload):void
    {
        $row = $payload['ticket'];
        $groupsMap = $this->getGroupsMap();

        $payload =[
            'row' => $row,
            'groups' => $groupsMap
        ];
        echo view('LeanGroups::tablePill',$payload);
    }


}
