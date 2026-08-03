<?php
/**
 * User Management Page
 * 
 * @var string $title
 * @var array $users
 * @var array $roles
 * @var array $filters
 */
?>

<?php $page_title = 'User Management'; ?>
<?php $active_page = 'users'; ?>

<div class="users-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-users me-2 text-primary"></i> User Management</h5>
            <p class="text-muted">Manage system users, roles, and permissions</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>/users/create" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i> Add User
            </a>
            <a href="<?php echo BASE_URL; ?>/users/roles" class="btn btn-outline-primary">
                <i class="fas fa-user-tag me-2"></i> Roles
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <select class="form-select" id="filterRole">
                    <option value="">All Roles</option>
                    <?php foreach ($roles ?? [] as $role): ?>
                        <option value="<?php echo $role->id; ?>"><?php echo htmlspecialchars($role->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterDepartment">
                    <option value="">All Departments</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                        <option value="<?php echo $dept->id; ?>"><?php echo htmlspecialchars($dept->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="searchUser" placeholder="Search users...">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> All Users</span>
            <span class="text-muted small"><?php echo count($users ?? []); ?> users</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php if ($user->profile_image): ?>
                                                    <img src="<?php echo UPLOADS_URL; ?>/<?php echo $user->profile_image; ?>" alt="Avatar">
                                                <?php else: ?>
                                                    <span><?php echo strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="user-name"><?php echo htmlspecialchars($user->full_name ?? $user->username); ?></div>
                                                <div class="user-username">@<?php echo htmlspecialchars($user->username); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->email); ?></td>
                                    <td>
                                        <span class="role-badge"><?php echo htmlspecialchars($user->role_display_name ?? 'User'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->department_name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $user->status; ?>">
                                            <?php echo ucfirst($user->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user->last_login): ?>
                                            <?php echo date('d M Y h:i A', strtotime($user->last_login)); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/users/<?php echo $user->id; ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/users/<?php echo $user->id; ?>/edit" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user->id != $current_user->id ?? 0): ?>
                                                <button class="btn btn-outline-danger delete-user" 
                                                        data-id="<?php echo $user->id; ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x d-block mb-2"></i>
                                    No users found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($total_pages) && $total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/users?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>/users?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo BASE_URL; ?>/users?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.users-container {
    padding: 0;
}

.filter-section {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94A3B8;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 8px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #DBEAFE;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar span {
    font-weight: 600;
    font-size: 14px;
    color: #2563EB;
}

.user-name {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.user-username {
    font-size: 12px;
    color: #94A3B8;
}

.role-badge {
    padding: 2px 12px;
    border-radius: 10px;
    background: #DBEAFE;
    color: #2563EB;
    font-size: 12px;
    font-weight: 500;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active { background: #D1FAE5; color: #10B981; }
.status-badge.inactive { background: #FEE2E2; color: #EF4444; }
.status-badge.suspended { background: #FEF3C7; color: #F59E0B; }
.status-badge.pending { background: #F1F5F9; color: #64748B; }

.enterprise-table thead th {
    font-weight: 600;
    font-size: 12px;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    background: #F8FAFC;
}

.enterprise-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #F1F5F9;
}

@media (max-width: 768px) {
    .user-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
$(document).ready(function() {
    // Filter functionality
    $('#filterRole, #filterStatus, #filterDepartment').on('change', applyFilters);
    $('#searchUser').on('keyup', applyFilters);
    
    function applyFilters() {
        const role = $('#filterRole').val();
        const status = $('#filterStatus').val();
        const department = $('#filterDepartment').val();
        const search = $('#searchUser').val().toLowerCase();
        
        $('.enterprise-table tbody tr').each(function() {
            const row = $(this);
            let show = true;
            
            if (role) {
                const rowRole = row.find('.role-badge').text().trim();
                if (rowRole !== $('#filterRole option:selected').text()) show = false;
            }
            if (status) {
                const rowStatus = row.find('.status-badge').text().trim().toLowerCase();
                if (rowStatus !== status) show = false;
            }
            if (search) {
                const text = row.text().toLowerCase();
                if (!text.includes(search)) show = false;
            }
            
            row.toggle(show);
        });
    }
    
    // Delete user
    $('.delete-user').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/api/users/' + id,
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