<?php

namespace Leantime\Plugins\LeanGroups\Services;

use Leantime\Plugins\LeanGroups\Repositories\LeanGroupsRepository;

class LeanGroups {

    private LeanGroupsRepository $repository;

    public function __construct() {
        $this->repository = new LeanGroupsRepository();
    }

    public function install(): void {
        $this->repository->setup();
    }

    public function uninstall(): void {
        $this->repository->teardown();
    }

}
