<div class="pageheader">
    <div class="pageicon"><span class="fa fa-users"></span></div>
    <div class="pagetitle">
        <h1>Lean Groups</h1>
        <small>Manage groups and memberships</small>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <div class="row-fluid">
            <div class="span8">
                <h3>Existing Groups</h3>
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Members</th>
                        <th>Description</th>
                        <th>Client</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($groups)){ ?>
                        <?php foreach ($groups as $g){ ?>
                            <tr onclick="window.location='<?= BASE_URL ?>/LeanGroups/groupMembership?group_id=<?= (int)$g['id'] ?>'" style="cursor:pointer;">
                                <td><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                                <td><?= (int)($g['member_count'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($g['description'] ?? '') ?></td>
                                <td><?php
                                    $clientLabel = '';
                                    if (!empty($g['client_name'])) { $clientLabel = (string)$g['client_name']; }
                                    elseif (!empty($g['client_id']) && !empty($clients)) {
                                        foreach ($clients as $c) {
                                            if ((int)($c['id'] ?? 0) === (int)$g['client_id']) { $clientLabel = (string)($c['name'] ?? ''); break; }
                                        }
                                    }
                                    echo htmlspecialchars($clientLabel !== '' ? $clientLabel : '');
                                ?></td>
                                <td>
                                    <form method="post" action="<?= BASE_URL ?>/LeanGroups/settings" onsubmit="return confirm('Remove group &quot;<?= htmlspecialchars($g['name']) ?>&quot;?');" style="display:inline;">
                                        <input type="hidden" name="_method" value="DELETE" />
                                        <input type="hidden" name="group_id" value="<?= (int)$g['id'] ?>" />
                                        <button type="submit" class="btn btn-danger btn-small"><i class="fa fa-trash"></i> Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php }else{ ?>
                        <tr><td colspan="6" style="text-align:center;">No groups yet</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div>
                <h3>Add Group</h3>
                <form method="post" action="<?= BASE_URL ?>/LeanGroups/settings">
                    <div class="control-group">
                        <label class="control-label" for="lg_name">Name</label>
                        <div class="controls">
                            <input type="text" id="lg_name" name="name" class="input-xlarge" required />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="lg_description">Description</label>
                        <div class="controls">
                            <textarea id="lg_description" name="description" class="input-xlarge" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="lg_client">Client (optional)</label>
                        <div class="controls">
                            <select id="lg_client" name="client_id" class="input-xlarge">
                                <option value="">-- None --</option>
                                <?php if (!empty($clients)) { foreach ($clients as $c) { ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php } } ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add Group</button>
                </form>
            </div>
            <div style="margin-top:20px;">
            <a class="btn" href="<?= BASE_URL ?>/plugins/myapps"><span class="fa fa-arrow-left"></span></a>
            </div>
        </div>

    </div>
</div>
