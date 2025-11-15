<div class="dropdown ticketDropdown groupIdDropdown show">
   <a class="dropdown-toggle f-left label-default" href="javascript:void(0);" role="button"
       id="groupDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php if ($row['group_id'] != null){ ?> style="background-color: <?php echo $groups[$row['group_id']]['color'];}?>" >
        <span class="text"><?php
                           if ($row['group_id'] != '' && $row['group_id'] > 0) {
                               echo $groups[$row['group_id']]['name'];
                           } else {
                               echo 'No Assignment Group';
                           } ?>
        </span>
        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <script>
        (function(){
            let sel = document.getElementById('groupDropdownMenuLink<?= $row['id']?>');
            if(!sel) return;
            function apply(){
                // Improve readability for very light backgrounds
                sel.style.color = isColorDark( sel.style.backgroundColor) ? '#fff': '#000';
            }
            sel.addEventListener('change', apply);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', apply);
            } else { apply(); }
        })();
    </script>

    <ul class="dropdown-menu" aria-labelledby="groupDropdownMenuLink<?= $row['id']?>">
        <li class="nav-header border">Select group</li>
        <li class="dropdown-item">
            <a  hx-swap='outerHTML' hx-target='#groupDropdownMenuLink<?= $row['id']?>' hx-post='/LeanGroups/pill?ticket_id=<?=$row['id']?>&group_id=0&group_name=No Assignment Group' >No Assignment Group</a>
        </li>
        <?php foreach ($groups as $groupId => $group) {
            echo "<li class='dropdown-item'>";
            echo "<a  hx-swap='outerHTML' hx-target='#groupDropdownMenuLink".$row['id']."' hx-post='/LeanGroups/pill?ticket_id=".$row['id']."&group_id=".$groupId."&group_name=".$group['name']."&color=".urlencode($group['color'])."' >" . $group['name'] . "</a>";
            echo '</li>';
        } ?>
    </ul>
</div>
