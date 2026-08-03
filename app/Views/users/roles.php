<?php
/**
 * Roles Management Page
 * 
 * @var string $title
 * @var array $roles
 * @var array $permissions
 */
?>

<?php $page_title = 'Roles & Permissions'; ?>
<?php $active_page = 'users'; ?>

<div class="roles-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-user-tag me-2 text-primary"></i> Roles & Permissions</h5>
            <p class="text-muted">Manage system roles and their permissions</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fas fa-plus me-2"></i> Add Role
            </button>
        </div>
    </div>
    
    <!-- Roles Cards -->
    <div class="row g-4">
        <?php if (!empty($roles)): ?>
            <?php foreach ($roles as $role): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="role-card">
                        <div class="role-header">
                            <div class="role-icon">
                                <i class="fas fa-<?php echo $role->is_system ? 'crown' : 'user-tag'; ?>"></i>
                            </div>
                            <div class="role-info">
                                <h6 class="role-name"><?php echo htmlspecialchars($role->display_name); ?></h6>
                                <span class="role-slug"><?php echo htmlspecialchars($role->name); ?></span>
                            </div>
                            <?php if ($role->is_system): ?>
                                <span class="badge bg-primary">System</span>
                            <?php endif; ?>
                        </div>
                        <div class="role-body">
                            <p class="role-description"><?php echo htmlspecialchars($role->description ?? 'No description'); ?></p>
                            <div class="role-meta">
                                <span><i class="fas fa-users me-1"></i> <?php echo $role->user_count ?? 0; ?> users</span>
                                <span><i class="fas fa-key me-1"></i> <?php echo $role->permission_count ?? 0; ?> permissions</span>
                            </div>
                            <div class="role-permissions">
                                <?php if (!empty($role->permissions)): ?>
                                    <?php foreach (array_slice($role->permissions, 0, 5) as $perm): ?>
                                        <span class="permission-tag"><?php echo htmlspecialchars($perm->display_name ?? $perm->name); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($role->permissions) > 5): ?>
                                        <span class="permission-tag more">+<?php echo count($role->permissions) - 5; ?> more</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">No permissions assigned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="role-footer">
                            <button class="btn btn-sm btn-outline-primary edit-role" data-id="<?php echo $role->id; ?>">
                                <i class="fas fa-edit me-1"></i> Edit Permissions
                            </button>
                            <?php if (!$role->is_system): ?>
                                <button class="btn btn-sm btn-outline-danger delete-role" data-id="<?php echo $role->id; ?>">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                    <h5>No Roles Found</h5>
                    <p class="text-muted">Create your first role to get started</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/users/roles">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., compliance_officer" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Display Name</label>
                        <input type="text" class="form-control" name="display_name" placeholder="e.g., Compliance Officer" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Role description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Role Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/users/roles/update" id="editRoleForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    <input type="hidden" name="role_id" id="editRoleId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Display Name</label>
                                <input type="text" class="form-control" name="display_name" id="editDisplayName">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="description" id="editDescription">
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-3 mb-2">Permissions</h6>
                    <div class="permissions-grid" id="permissionsGrid">
                        <!-- Permissions will be loaded via JS -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.roles-container {
    padding: 0;
}

.role-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.3s;
    height: 100%;
}

.role-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.role-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.role-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #DBEAFE;
    color: #2563EB;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.role-info {
    flex: 1;
}

.role-name {
    font-weight: 600;
    color: #1E293B;
    margin: 0;
}

.role-slug {
    font-size: 12px;
    color: #94A3B8;
    font-family: 'Courier New', monospace;
}

.role-description {
    color: #64748B;
    font-size: 14px;
    margin: 0 0 12px;
}

.role-meta {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #94A3B8;
    margin-bottom: 12px;
}

.role-permissions {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.permission-tag {
    padding: 2px 10px;
    border-radius: 12px;
    background: #F1F5F9;
    color: #64748B;
    font-size: 11px;
}

.permission-tag.more {
    background: #2563EB;
    color: #fff;
}

.role-footer {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #F1F5F9;
    display: flex;
    gap: 8px;
}

.permissions-grid {
    max-height: 400px;
    overflow-y: auto;
    padding: 8px 0;
}

.permission-group {
    margin-bottom: 12px;
}

.permission-group-title {
    font-weight: 600;
    color: #1E293B;
    font-size: 14px;
    padding: 4px 0;
    border-bottom: 1px solid #F1F5F9;
    margin-bottom: 8px;
}

.permission-items {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.permission-item:hover {
    background: #F8FAFC;
}

.permission-item .form-check-input {
    margin: 0;
}

.permission-item .form-check-label {
    font-size: 13px;
    color: #64748B;
    cursor: pointer;
}

.empty-state i {
    display: block;
}

@media (max-width: 768px) {
    .permission-items {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
$(document).ready(function() {
    // Edit role button
    $('.edit-role').on('click', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/roles/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const role = response.data;
                    $('#editRoleId').val(role.id);
                    $('#editDisplayName').val(role.display_name);
                    $('#editDescription').val(role.description || '');
                    
                    // Load permissions
                    loadPermissions(id);
                    
                    $('#editRoleModal').modal('show');
                }
            }
        });
    });
    
    function loadPermissions(roleId) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/permissions/' + roleId,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const permissions = response.data;
                    const grid = $('#permissionsGrid');
                    grid.empty();
                    
                    // Group permissions by module
                    const grouped = {};
                    permissions.forEach(function(perm) {
                        const module = perm.module || 'general';
                        if (!grouped[module]) grouped[module] = [];
                        grouped[module].push(perm);
                    });
                    
                    // Render permission groups
                    Object.keys(grouped).forEach(function(module) {
                        const group = $('<div class="permission-group"></div>');
                        group.append(`<div class="permission-group-title">${module.charAt(0).toUpperCase() + module.slice(1)}</div>`);
                        
                        const items = $('<div class="permission-items"></div>');
                        grouped[module].forEach(function(perm) {
                            const checked = perm.assigned ? 'checked' : '';
                            items.append(`
                                <div class="permission-item">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                           value="${perm.id}" ${checked}>
                                    <label class="form-check-label">${perm.display_name}</label>
                                </div>
                            `);
                        });
                        
                        group.append(items);
                        grid.append(group);
                    });
                }
            }
        });
    }
    
    // Delete role
    $('.delete-role').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this role?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/roles/' + id,
                method: 'DELETE',
                data: {
                    _csrf: '<?php echo $csrf_token ?? ''; ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>