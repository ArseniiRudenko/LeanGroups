<div class="form-group tw-flex tw-w-3/5">
    <label class="control-label tw-mx-m tw-w-[100px]">Group</label>
    <div class="">

        <select data-placeholder="Filter by group" style="width:175px;"
                name="group_id" id="group_id" class="user-select tw-mr-sm">
            <option value="">No assigment group</option>
            <?php foreach ($groups as $groupRow) { ?>
                <?php echo "<option value='".$groupRow['id']."'";

                if ($ticketGroup == $groupRow['id']) {
                    echo " selected='selected' ";
                }

                echo '>'.$groupRow['name'].'</option>'; ?>

            <?php } ?>
        </select>&nbsp;
    </div>
</div>
