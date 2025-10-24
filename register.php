<?php

use Illuminate\Support\Facades\View;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Plugins\LeanGroups\Repositories\LeanGroupsRepository;

// Register view namespace for LeanGroups plugin
View::addNamespace('LeanGroups', base_path('app/Plugins/LeanGroups/Templates'));

// provide access to projects via groups
EventDispatcher::add_filter_listener('leantime.domain.projects.*.afterLoadingProjects', 'addProjects');
EventDispatcher::add_event_listener('leantime.domain.projects.*.isUserAssignedToProject', 'isUserAssignedToProject');
EventDispatcher::add_event_listener('leantime.domain.projects.*.userProjectRole', 'userProjectRole');

function addProjects($projects, $params): array
{
    $repository = new LeanGroupsRepository();
    $userId = (int) $params['userId'];
    $projectStatus = $params['projectStatus'];
    $accessStatus = $params['accessStatus'];
    $clientId = $params['clientId'];
    $projectsFromGroups = $repository->getProjectForUser($userId,$projectStatus,$accessStatus,$clientId);
    //merge projects with deduplication by id
    $allProjects = array_merge($projects, $projectsFromGroups);
    $uniqueProjects = [];
    foreach ($allProjects as $project) {
        $uniqueProjects[$project['id']] = $project;
    }
    return array_values($uniqueProjects);
}

function isUserAssignedToProject($result, $params): bool{
    $userId = (int) $params['userId'];
    $projectId = (int) $params['projectId'];
    $repository = new LeanGroupsRepository();
    $isAssignedViaGroup = $repository->isUserInProjectViaGroup($userId, $projectId);
    return $result || $isAssignedViaGroup;
}

function userProjectRole($result, $params): string{
    $userId = (int) $params['userId'];
    $projectId = (int) $params['projectId'];
    $repository = new LeanGroupsRepository();
    $roleViaGroup = $repository->getUserProjectRoleViaGroup($userId, $projectId);
    if( $roleViaGroup !== null ){
       if($result !== ''){
           return  max($result, $roleViaGroup);
       }else{
           return $roleViaGroup;
       }
    }
    return $result;
}


