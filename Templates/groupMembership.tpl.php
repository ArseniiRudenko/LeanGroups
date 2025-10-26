<?php
$colorMap = [
    'default'=> 'var(--grey)',
    'primary'=> 'var(--primary-color)',
    'red'=> 'var(--red)',
    'green'=> 'var(--green)',
    'yellow'=> 'var(--yellow)',
    'black'=> '#000',
    "orange" => "#f57c00",
    "blue" => "#007bff",
    "pink" => "#e83e8c",
    "purple" => "#6f42c1",
    "teal" => "#20c997",
    "cyan" => "#17a2b8",
    "gray-dark" => "#343a40"
];

$inverseColorMap = array_flip($colorMap);

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-user-group"></span></div>
    <div class="pagetitle">
        <h1>Group: <?= htmlspecialchars($group['name'] ?? '') ?></h1>
        <small>Manage memberships and group details</small>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <div class="tab-pane">
            <ul id="lg-tabs" class="nav nav-tabs tw-flex-row">
                <li class="active nav-item"><a href="#tab-members" data-toggle="tab">Members</a></li>
                <li class="nav-item"><a href="#tab-projects" data-toggle="tab">Projects</a></li>
                <li class="nav-item"><a href="#tab-edit" data-toggle="tab">Edit Group</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="tab-members">
                    <div class="row-fluid" style="margin-top:15px;">
                        <div class="span7">
                            <h3>Current Members</h3>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width:60px;">Avatar</th>
                                        <th>First name</th>
                                        <th>Last name</th>
                                        <th>Email</th>
                                        <th style="width:120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($members)): ?>
                                    <?php foreach ($members as $m): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= BASE_URL ?>/users/editUser/<?= (int)$m['id'] ?>" title="View user" class="tw-flex tw-items-center tw-gap-2">
                                                    <img alt="User" src="<?= BASE_URL ?>/api/users?profileImage=<?= (int)$m['id'] ?>&v=<?= time() ?>" style="width:24px;height:24px;border-radius:50%;"/>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($m['firstname'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($m['lastname'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($m['username'] ?? '') ?></td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership" onsubmit="return confirm('Remove member from group?');" style="display:inline;">
                                                    <input type="hidden" name="action" value="remove_member" />
                                                    <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                                    <input type="hidden" name="user_id" value="<?= (int)$m['id'] ?>" />
                                                    <button class="btn btn-danger btn-small"><i class="fa fa-user-minus"></i> Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center;">No members in this group</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <h3>Add Member</h3>
                            <p>Select a user to add to this group.</p>
                            <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership">
                                <input type="hidden" name="action" value="add_member" />
                                <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                <div class="control-group">
                                    <label class="control-label" for="lg_user_id">User</label>
                                    <div class="controls tw-flex tw-items-center tw-gap-2">
                                        <select id="lg_user_id" name="user_id" class="input-xlarge" required>
                                            <option value="">-- Select User --</option>
                                            <?php if (!empty($allUsers)) { foreach ($allUsers as $u) {
                                                $fullName = trim(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? ''));
                                                if ($fullName === '') { $fullName = $u['username'] ?? ('User #'.(int)$u['id']); }
                                            ?>
                                                <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($fullName) ?><?= !empty($u['username']) ? ' ('.htmlspecialchars($u['username']).')' : '' ?></option>
                                            <?php } } ?>
                                        </select>
                                        <!-- Avatar preview, modeled after AuditTrail usage -->
                                        <img id="lg_user_avatar" alt="User" src="" style="width:24px;height:24px;border-radius:50%;display:none;"/>
                                    </div>
                                </div>
                                <button class="btn btn-primary"><i class="fa fa-user-plus"></i> Add</button>

                            </form>
                        </div>
                        <div style="margin-top: 20px">
                            <a class="btn" href="<?= BASE_URL ?>/LeanGroups/settings"><span class="fa fa-arrow-left"></span></a>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-projects">
                    <div class="row-fluid" style="margin-top:15px;">
                        <div class="span7">
                            <h3>Assigned Projects</h3>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th style="width:160px;">Role</th>
                                        <th style="width:220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['name'] ?? ('Project #'.(int)$p['id'])) ?></td>
                                            <td>
                                                <?php
                                                $currentRoleKey = isset($p['role']) ? (int)$p['role'] : null;
                                                $currentRoleLabel = ($currentRoleKey !== null && isset($roles[$currentRoleKey])) ? $roles[$currentRoleKey] : '';
                                                ?>
                                                <?= htmlspecialchars($currentRoleLabel) ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership" style="display:inline-block; margin-right:6px;">
                                                    <input type="hidden" name="action" value="set_role" />
                                                    <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                                    <input type="hidden" name="project_id" value="<?= (int)$p['id'] ?>" />
                                                    <select name="role_key" class="input-small">
                                                        <option value="">-- None --</option>
                                                        <?php foreach ($roles as $rk => $rlabel): ?>
                                                            <option value="<?= (int)$rk ?>" <?= ((string)$rk === (string)($p['role'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars($rlabel) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-primary btn-small"><i class="fa fa-save"></i></button>
                                                </form>
                                                <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership" onsubmit="return confirm('Remove project from group?');" style="display:inline-block;">
                                                    <input type="hidden" name="action" value="remove_project" />
                                                    <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                                    <input type="hidden" name="project_id" value="<?= (int)$p['id'] ?>" />
                                                    <button class="btn btn-danger btn-small"><i class="fa fa-minus-circle"></i> Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center;">No projects assigned to this group</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <h3>Add Project</h3>
                            <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership">
                                <input type="hidden" name="action" value="add_project" />
                                <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                <div class="control-group">
                                    <label class="control-label" for="g_add_project">Project</label>
                                    <div class="controls">
                                        <select id="g_add_project" name="project_id" class="input-xlarge" required>
                                            <option value="">-- Select Project --</option>
                                            <?php if (!empty($allProjects)) { foreach ($allProjects as $ap) { ?>
                                                <option value="<?= (int)$ap['id'] ?>"><?= htmlspecialchars($ap['name']) ?></option>
                                            <?php } } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="g_add_role">Role (optional)</label>
                                    <div class="controls">
                                        <select id="g_add_role" name="role_key" class="input-medium">
                                            <option value="">-- None --</option>
                                            <?php foreach ($roles as $rk => $rlabel): ?>
                                                <option value="<?= (int)$rk ?>"><?= htmlspecialchars($rlabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <button class="btn btn-primary"><i class="fa fa-plus"></i> Add Project</button>
                            </form>
                            <div style="margin-top: 20px">
                                <a class="btn" href="<?= BASE_URL ?>/LeanGroups/settings"><span class="fa fa-arrow-left"></span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-edit">
                    <div class="row-fluid" style="margin-top:15px;">
                        <div class="span6">
                            <h3>Group Details</h3>
                            <form method="post" action="<?= BASE_URL ?>/LeanGroups/groupMembership">
                                <input type="hidden" name="action" value="update_group" />
                                <input type="hidden" name="group_id" value="<?= (int)($group['id'] ?? 0) ?>" />
                                <div class="control-group">
                                    <label class="control-label" for="g_name">Name</label>
                                    <div class="controls">
                                        <input type="text" id="g_name" name="name" value="<?= htmlspecialchars($group['name'] ?? '') ?>" class="input-xlarge" required />
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="g_description">Description</label>
                                    <div class="controls">
                                        <textarea id="g_description" name="description" rows="4" class="input-xlarge"><?= htmlspecialchars($group['description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="g_client">Client (optional)</label>
                                    <div class="controls">
                                        <select id="g_client" name="client_id" class="input-xlarge">
                                            <option value="">-- None --</option>
                                            <?php $currentClientId = isset($group['client_id']) ? (int)$group['client_id'] : null; ?>
                                            <?php if (!empty($clients)) { foreach ($clients as $c) { ?>
                                                <option value="<?= (int)$c['id'] ?>" <?= ($currentClientId !== null && (int)$c['id'] === $currentClientId) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                            <?php } } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="lg_color">Color</label>
                                    <div class="controls">
                                        <select id="lg_color" name="color" class="input-xlarge">
                                            <?php foreach ($colorMap as $c => $v ) { ?>
                                                <option value="<?= $v ?>" <?= $v === $group['color'] ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                    <script>
                                        (function(){
                                        var sel = document.getElementById('lg_color');
                                        if(!sel) return;
                                        function apply(){
                                        sel.style.backgroundColor = sel.value;
                                        // Improve readability for very light backgrounds
                                        sel.style.color = isColorDark(sel.value) ? '#fff': '#000';
                                    }
                                        sel.addEventListener('change', apply);
                                        if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', apply);
                                    } else { apply(); }
                                    })();
                                </script>
                                <button class="btn btn-success"><i class="fa fa-save"></i> Save Changes</button>
                            </form>
                            <div style="margin-top: 20px">
                                <a class="btn" href="<?= BASE_URL ?>/LeanGroups/settings"><span class="fa fa-arrow-left"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
