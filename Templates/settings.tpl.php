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
                        <th>Color</th>
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
                                <td><span class="label" style="background-color: <?= $inverseColorMap[$g['color']] ?>" ><?= htmlspecialchars($inverseColorMap[$g['color']]) ?></span></td>
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
                        <label class="control-label" for="lg_color">Color</label>
                        <div class="controls">
                            <select id="lg_color" name="color" class="input-xlarge">
                                <?php foreach ($colorMap as $c => $v ) { ?>
                                    <option value="<?= $v ?>" <?= $c === 'default' ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <script>
                        (function(){
                            function isColorDark(color, element = document.body) {
                                let resolvedColor = color;

                                // --- 1️⃣ Resolve CSS variable ---
                                if (color.startsWith('var(')) {
                                    const varName = color.slice(4, -1).trim();
                                    resolvedColor = getComputedStyle(element).getPropertyValue(varName).trim();
                                }

                                // --- 2️⃣ Create a temporary element to resolve any CSS color value ---
                                const div = document.createElement('div');
                                div.style.color = resolvedColor;
                                document.body.appendChild(div);
                                const computed = getComputedStyle(div).color;
                                document.body.removeChild(div);

                                // computed is always like "rgb(r, g, b)"
                                const [r, g, b] = computed.match(/\d+/g).map(Number);

                                // --- 3️⃣ Compute luminance ---
                                const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;

                                return luminance < 128;
                            }
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
