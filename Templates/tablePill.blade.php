<td data-order="<?= $row['group_id'] ?>">
    <div class="dropdown ticketDropdown groupIdDropdown show">
        <a class="dropdown-toggle f-left label-default" href="javascript:void(0);" role="button"
           id="groupDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="text" id="groupName<?= $row['id']?>"><?php
                                                          if ($row['group_id'] != '' && $row['group_id'] > 0) {
                                                              echo $groups[$row['group_id']]['name'];
                                                          } else {
                                                              echo 'No Group';
                                                          } ?>
        </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="groupDropdownMenuLink<?= $row['id']?>">
            <li class="nav-header border">Select group</li>
            <li class="dropdown-item">
                <a hx-target='#groupName<?= $row['id']?>' hx-post='/LeanGroups/pill?ticket_id=<?=$row['id']?>&group_id=0&group_name=No Group' >No Assignment Group</a>
            </li>
            <?php foreach ($groups as $groupId => $group) {
                echo "<li class='dropdown-item'>";
                echo "<a hx-target='#groupName".$row['id']."' hx-post='/LeanGroups/pill?ticket_id=".$row['id']."&group_id=".$groupId."&group_name=".$group['name']."' >" . $group['name'] . "</a>";
                echo '</li>';
            } ?>
        </ul>
    </div>

</td>
