<script>
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
</script>

<td data-order="<?= $row['group_id'] ?>">
    <div class="dropdown ticketDropdown groupIdDropdown show">
        <a class="dropdown-toggle f-left label-default" href="javascript:void(0);" role="button"
           id="groupDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php if ($row['group_id'] != null){ ?> style="background-color: <?php echo $groups[$row['group_id']]['color'];  }?>">
        <span class="text" id="groupName<?= $row['id']?>"><?php
                                                          if ($row['group_id'] != '' && $row['group_id'] > 0) {
                                                              echo $groups[$row['group_id']]['name'];
                                                          } else {
                                                              echo 'No Group';
                                                          } ?>
        </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
            <script>
                (function(){
                    var sel = document.getElementById('groupDropdownMenuLink<?= $row['id']?>');
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
