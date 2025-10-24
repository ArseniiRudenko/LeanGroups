<?php

namespace Leantime\Plugins\LeanGroups\Repositories;

use Leantime\Core\Db\Db;
use PDO;

class LeanGroupsRepository{

    private Db $db;

    public function __construct()
    {
        // Get DB Instance
        $this->db = app(Db::class);
    }

    public function setup():void{
        // Create tables
        $sql1="
            CREATE TABLE IF NOT EXISTS lean_groups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                client_id INT,
                created_at TIMESTAMP DEFAULT UTC_TIMESTAMP
            );
        ";

        $sql2="
            CREATE TABLE IF NOT EXISTS lean_group_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                group_id INT NOT NULL,
                user_id INT NOT NULL,
                joined_at TIMESTAMP DEFAULT UTC_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES lean_groups(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES zp_user(id) ON DELETE CASCADE
            );
        ";

        $sql3="
            CREATE TABLE IF NOT EXISTS lean_group_project_membership(
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT NOT NULL,
                group_id INT NOT NULL,
                role INT,
                FOREIGN KEY (group_id) REFERENCES lean_groups(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES zp_projects(id) ON DELETE CASCADE
            )
        ";
        //IF NOT EXISTS for column is a supported construction in mariadb, the IDE is wrong, do not remove!
        $sql4= 'ALTER TABLE zp_tickets ADD COLUMN IF NOT EXISTS group_id INT NULL';
        $sql5 = 'alter table zp_tickets add constraint fk_group_id_lean_groups_id foreign key (group_id) references lean_groups(id) on delete set null';

        $pdo = $this->db->pdo();
        $pdo->exec($sql1);
        $pdo->exec($sql2);
        $pdo->exec($sql3);
        $pdo->exec($sql4);
        $pdo->exec($sql5);
    }

    public function teardown():void{
        $pdo = $this->db->pdo();
        $pdo->exec("DROP TABLE IF EXISTS lean_groups");
        $pdo->exec("DROP TABLE IF EXISTS lean_group_members");
        $pdo->exec("DROP TABLE IF EXISTS lean_group_project_membership");
        $pdo->exec("alter table zp_tickets drop constraint fk_group_id_lean_groups_id");
        $pdo->exec("alter table zp_tickets drop column group_id;");
    }

    // Fetch all groups with member count and latest role (if any) and client name if available
    public function getGroups(): array {
        $sql = "
            SELECT g.id, g.name, g.description, g.client_id,
                   c.name AS client_name,
                   COALESCE(m.member_count, 0) AS member_count,
                   pm.role AS role
            FROM lean_groups g
            LEFT JOIN (
                SELECT group_id, COUNT(*) AS member_count
                FROM lean_group_members
                GROUP BY group_id
            ) m ON m.group_id = g.id
            LEFT JOIN zp_clients c ON c.id = g.client_id
            LEFT JOIN (
                SELECT group_id, MAX(role) AS role
                FROM lean_group_project_membership
                GROUP BY group_id
            ) pm ON pm.group_id = g.id
            ORDER BY g.name ASC
        ";
        $pdo = $this->db->pdo();
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createGroup(string $name, ?string $description = null, ?int $clientId = null): int {
        $sql = "INSERT INTO lean_groups (name, description, client_id) VALUES (:name, :description, :client_id)";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':client_id', $clientId, $clientId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        return (int)$pdo->lastInsertId();
    }

    public function deleteGroup(int $groupId): void {
        $pdo = $this->db->pdo();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM lean_group_members WHERE group_id = :gid");
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM lean_group_project_membership WHERE group_id = :gid");
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM lean_groups WHERE id = :gid");
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();
    }

    public function getGroup(int $groupId): ?array {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare("SELECT * FROM lean_groups WHERE id = :id");
        $stmt->bindValue(':id', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateGroup(int $groupId, array $data): void {
        $fields = [];
        $params = ['id' => $groupId];
        if (array_key_exists('name', $data)) { $fields[] = 'name = :name'; $params['name'] = $data['name']; }
        if (array_key_exists('description', $data)) { $fields[] = 'description = :description'; $params['description'] = $data['description']; }
        if (array_key_exists('client_id', $data)) { $fields[] = 'client_id = :client_id'; $params['client_id'] = $data['client_id']; }
        if (!$fields) { return; }
        $sql = 'UPDATE lean_groups SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $pdo = $this->db->pdo();
        $call =$pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $call->bindValue(':' . $key, $value);
        }
        $call->execute();
    }

    public function getGroupMembers(int $groupId): array {
        $sql = "
            SELECT u.id,  u.username, u.firstname,u.lastname
            FROM zp_user u
            INNER JOIN lean_group_members gm ON gm.user_id = u.id
            WHERE gm.group_id = :gid
            ORDER BY u.firstname ASC
        ";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addMember(int $groupId, int $userId): void {
        $pdo = $this->db->pdo();
        $call = $pdo->prepare("INSERT INTO lean_group_members (group_id, user_id) VALUES (:gid, :uid)");
        $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $call->bindValue(':uid', $userId, PDO::PARAM_INT);
        $call->execute();
    }

    public function removeMember(int $groupId, int $userId): void {
        $pdo = $this->db->pdo();
        $call = $pdo->prepare("DELETE FROM lean_group_members WHERE group_id = :gid AND user_id = :uid");
        $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $call->bindValue(':uid', $userId, PDO::PARAM_INT);
        $call->execute();
    }

    public function setGroupRoleForProject(int $groupId, int $projectId, ?int $roleKey): void {
        $pdo = $this->db->pdo();
        // Upsert-like behavior: delete if role null, otherwise insert/update
        if ($roleKey === null) {
            $call = $pdo->prepare("DELETE FROM lean_group_project_membership WHERE group_id = :gid AND project_id = :pid");
            $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
            $call->bindValue(':pid', $projectId, PDO::PARAM_INT);
            $call->execute();
            return;
        }
        // Try update first
        $call = $pdo->prepare("UPDATE lean_group_project_membership SET role = :role WHERE group_id = :gid AND project_id = :pid");
        $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $call->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $call->bindValue(':role', $roleKey, PDO::PARAM_INT);
        $call->execute();
        $affected = $call->rowCount();
        if ($affected === 0) {
            $call = $pdo->prepare("INSERT INTO lean_group_project_membership (group_id, project_id, role) VALUES (:gid, :pid, :role)");
            $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
            $call->bindValue(':pid', $projectId, PDO::PARAM_INT);
            $call->bindValue(':role', $roleKey, PDO::PARAM_INT);
            $call->execute();
        }
    }

    public function getGroupProjects(int $groupId): array {
        $sql = "
            SELECT p.id, p.name, pm.role
            FROM lean_group_project_membership pm
            INNER JOIN zp_projects p ON p.id = pm.project_id
            WHERE pm.group_id = :gid
            ORDER BY p.name ASC
        ";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addProjectToGroup(int $groupId, int $projectId): void {
        $pdo = $this->db->pdo();
        $sql = "INSERT INTO lean_group_project_membership (group_id, project_id, role)
                SELECT :gid, :pid, NULL FROM DUAL
                WHERE NOT EXISTS (
                    SELECT 1 FROM lean_group_project_membership WHERE group_id = :gid2 AND project_id = :pid2
                )";
        $call = $pdo->prepare($sql);
        $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $call->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $call->bindValue(':gid2', $groupId, PDO::PARAM_INT);
        $call->bindValue(':pid2', $projectId, PDO::PARAM_INT);
        $call->execute();
    }

    public function removeProjectFromGroup(int $groupId, int $projectId): void {
        $pdo = $this->db->pdo();
        $call = $pdo->prepare("DELETE FROM lean_group_project_membership WHERE group_id = :gid AND project_id = :pid");
        $call->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $call->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $call->execute();
    }

    /**
     * Get list of all users for dropdown selection
     * @return array<int,array<string,mixed>>
     */
    public function getAllUsers(): array {
        $sql = "
            SELECT u.id, u.username, u.firstname, u.lastname
            FROM zp_user u
            ORDER BY u.firstname ASC, u.lastname ASC
        ";
        $pdo = $this->db->pdo();
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get list of users that are NOT already members of the given group
     * @param int $groupId
     * @return array<int,array<string,mixed>>
     */
    public function getAllUsersNotInGroup(int $groupId): array {
        $sql = "
            SELECT u.id, u.username, u.firstname, u.lastname
            FROM zp_user u
            WHERE NOT EXISTS (
                SELECT 1 FROM lean_group_members gm
                WHERE gm.group_id = :gid AND gm.user_id = u.id
            )
            ORDER BY u.firstname ASC, u.lastname ASC
        ";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':gid', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProjectForUser(int $userId,string $projectStatus,string $accessStatus, int|string $clientId): array
    {
        $query = "
           SELECT
					project.id,
					project.name,
					project.details,
					project.clientId,
					project.state,
					project.hourBudget,
					project.dollarBudget,
				    project.menuType,
				    project.type,
				    project.parent,
				    project.modified,
				    project.start,
				    project.end,
					client.name AS clientName,
					client.id AS clientId,
					parent.id AS parentId,
					parent.name as parentName,
					IF(favorite.id IS NULL, false, true) as isFavorite
					FROM zp_projects as project
                    LEFT JOIN zp_projects as parent ON parent.id = project.parent
                    LEFT JOIN zp_reactions as favorite ON project.id = favorite.moduleId
				                                          AND favorite.module = 'project'
				                                          AND favorite.reaction = 'favorite'
				                                          AND favorite.userId = :id
                    LEFT JOIN zp_clients as client ON project.clientId = client.id
					join lean_group_members as group_member on group_member.user_id = :id
					join leantime.lean_groups lg on group_member.group_id = lg.id
                    left join lean_group_project_membership as membership on membership.project_id = project.id and membership.group_id = group_member.group_id
           WHERE membership.project_id IS NOT NULL
        ";

        if ($accessStatus == 'clients' || $accessStatus == 'all') {
            $query .=" or (project.psettings = 'clients' and lg.client_id = client.id)";
        }

        if ($projectStatus == 'open') {
            $query .= " AND (project.state <> '-1' OR project.state IS NULL)";
        } elseif ($projectStatus == 'closed') {
            $query .= ' AND (project.state = -1)';
        }

        if ($clientId != '' && $clientId != null && $clientId > 0) {
            $query .= ' AND project.clientId = :clientId';
        }

        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($query);

        if ($clientId != '' && $clientId != null && $clientId > 0) {
            $stmt->bindValue(':clientId', $clientId);
        }

        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function isUserInProjectViaGroup(int $userId, int $projectId): bool
    {
        $sql = "SELECT 1 FROM lean_group_members
                WHERE user_id = :userId
                AND group_id IN (
                  SELECT group_id
                  FROM lean_group_project_membership
                  WHERE project_id = :projectId
                )";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':projectId', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    public function getUserProjectRoleViaGroup(int $userId, int $projectId)
    {
        $sql = "SELECT pm.role FROM lean_group_members gm
                JOIN lean_group_project_membership pm ON gm.group_id = pm.group_id
                WHERE gm.user_id = :userId AND pm.project_id = :projectId and pm.role IS NOT NULL
                ORDER BY pm.role DESC
                LIMIT 1";
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':projectId', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $role = $stmt->fetchColumn();
        return $role !== false ? $role : null;
    }


}
