<?php
/**
 * User Profile Page
 * 
 * @var string $title
 * @var object $user
 * @var array $stats
 * @var array $activities
 */
?>

<?php $page_title = 'My Profile'; ?>
<?php $active_page = 'profile'; ?>

<div class="profile-container">
    <div class="row g-4">
        <!-- Profile Sidebar -->
        <div class="col-xl-3 col-lg-4">
            <!-- Profile Card -->
            <div class="card profile-card">
                <div class="profile-cover">
                    <div class="profile-avatar-wrapper">
                        <?php if (isset($user) && !empty($user->profile_image)): ?>
                            <img src="<?php echo UPLOADS_URL; ?>/<?php echo $user->profile_image; ?>" 
                                 alt="Profile" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar placeholder">
                                <span><?php echo isset($user) ? strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)) : 'U'; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h5 class="profile-name"><?php echo isset($user) ? htmlspecialchars($user->full_name ?? $user->username) : 'User'; ?></h5>
                    <p class="profile-role-text">
                        <?php echo isset($user) ? ucfirst(str_replace('_', ' ', $user->role_display_name ?? 'User')) : 'User'; ?>
                    </p>
                    <p class="profile-email">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo isset($user) ? htmlspecialchars($user->email) : ''; ?>
                    </p>
                    
                    <div class="profile-stats mt-3">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['total_activities'] ?? 0; ?></span>
                            <span class="stat-label">Activities</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['total_compliance'] ?? 0; ?></span>
                            <span class="stat-label">Compliance</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['total_risks'] ?? 0; ?></span>
                            <span class="stat-label">Risks</span>
                        </div>
                    </div>
                    
                    <div class="profile-actions mt-3">
                        <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Quick Actions</h6>
                    <div class="quick-actions-list">
                        <a href="<?php echo BASE_URL; ?>/profile/change-password" class="quick-action-item">
                            <i class="fas fa-key text-primary"></i>
                            <span>Change Password</span>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/profile/2fa" class="quick-action-item">
                            <i class="fas fa-shield-alt text-success"></i>
                            <span>Two-Factor Authentication</span>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/settings/security" class="quick-action-item">
                            <i class="fas fa-lock text-warning"></i>
                            <span>Security Settings</span>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/settings/notifications" class="quick-action-item">
                            <i class="fas fa-bell text-info"></i>
                            <span>Notification Preferences</span>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Activity Overview -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2"></i> Activity Overview</span>
                    <span class="text-muted small">Last 30 days</span>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i> Recent Activities</span>
                    <a href="<?php echo BASE_URL; ?>/audit" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="activity-timeline">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon <?php echo $activity->type ?? 'info'; ?>">
                                        <i class="fas fa-<?php echo $activity->icon ?? 'circle'; ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title"><?php echo htmlspecialchars($activity->description); ?></div>
                                        <div class="timeline-meta">
                                            <span class="timeline-time">
                                                <i class="far fa-clock me-1"></i>
                                                <?php echo time_ago($activity->created_at); ?>
                                            </span>
                                            <?php if ($activity->module): ?>
                                                <span class="timeline-module">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo ucfirst($activity->module); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x"></i>
                                <p class="mt-2">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-user-circle me-2"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Username</label>
                                <div class="info-value"><?php echo htmlspecialchars($user->username ?? ''); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Employee ID</label>
                                <div class="info-value"><?php echo htmlspecialchars($user->employee_id ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Department</label>
                                <div class="info-value"><?php echo htmlspecialchars($user->department_name ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Role</label>
                                <div class="info-value">
                                    <span class="role-badge"><?php echo ucfirst(str_replace('_', ' ', $user->role_display_name ?? 'User')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Account Status</label>
                                <div class="info-value">
                                    <span class="status-badge <?php echo $user->status ?? 'active'; ?>">
                                        <?php echo ucfirst($user->status ?? 'Active'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Member Since</label>
                                <div class="info-value"><?php echo date('d M Y', strtotime($user->created_at ?? 'now')); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Last Login</label>
                                <div class="info-value"><?php echo $user->last_login ? date('d M Y h:i A', strtotime($user->last_login)) : 'Never'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">2FA Status</label>
                                <div class="info-value">
                                    <?php if ($user->two_factor_enabled ?? false): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Enabled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i> Disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/profile/update" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" 
                                   value="<?php echo htmlspecialchars($user->first_name ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" 
                                   value="<?php echo htmlspecialchars($user->last_name ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user->email ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" 
                                   value="<?php echo htmlspecialchars($user->mobile ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user->address ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" 
                                   value="<?php echo htmlspecialchars($user->city ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State/Province</label>
                            <input type="text" class="form-control" name="state" 
                                   value="<?php echo htmlspecialchars($user->state ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" name="postal_code" 
                                   value="<?php echo htmlspecialchars($user->postal_code ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container {
    padding: 0;
}

.profile-card {
    overflow: hidden;
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.profile-cover {
    height: 100px;
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    position: relative;
}

.profile-avatar-wrapper {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid #FFFFFF;
    object-fit: cover;
    background: #FFFFFF;
}

.profile-avatar.placeholder {
    background: #DBEAFE;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    color: #2563EB;
}

.profile-name {
    font-size: 20px;
    font-weight: 600;
    color: #1E293B;
    margin: 16px 0 4px;
}

.profile-role-text {
    color: #64748B;
    font-size: 14px;
    margin-bottom: 4px;
}

.profile-email {
    color: #94A3B8;
    font-size: 14px;
    margin: 0;
}

.profile-stats {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 12px 0;
    border-top: 1px solid #F1F5F9;
    border-bottom: 1px solid #F1F5F9;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #1E293B;
}

.stat-label {
    display: block;
    font-size: 12px;
    color: #94A3B8;
}

.stat-divider {
    width: 1px;
    height: 30px;
    background: #E2E8F0;
    margin: 0 16px;
}

.profile-actions {
    padding-top: 12px;
}

.quick-actions-list {
    margin-top: 8px;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    color: #1E293B;
    text-decoration: none;
    border-bottom: 1px solid #F1F5F9;
    transition: all 0.2s;
}

.quick-action-item:last-child {
    border-bottom: none;
}

.quick-action-item:hover {
    color: #2563EB;
    padding-left: 8px;
}

.quick-action-item i:first-child {
    width: 24px;
    font-size: 16px;
}

.quick-action-item span {
    flex: 1;
    font-size: 14px;
}

.activity-timeline {
    padding: 16px 20px;
}

.timeline-item {
    display: flex;
    gap: 16px;
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.timeline-icon.success { background: #D1FAE5; color: #10B981; }
.timeline-icon.warning { background: #FEF3C7; color: #F59E0B; }
.timeline-icon.danger { background: #FEE2E2; color: #EF4444; }
.timeline-icon.info { background: #DBEAFE; color: #3B82F6; }

.timeline-content {
    flex: 1;
}

.timeline-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.timeline-meta {
    display: flex;
    gap: 16px;
    margin-top: 4px;
    font-size: 12px;
    color: #94A3B8;
}

.timeline-meta i {
    margin-right: 4px;
}

.info-item {
    padding: 8px 0;
}

.info-label {
    display: block;
    font-size: 12px;
    color: #94A3B8;
    font-weight: 500;
    margin-bottom: 2px;
}

.info-value {
    font-size: 14px;
    color: #1E293B;
}

.role-badge {
    padding: 4px 12px;
    border-radius: 12px;
    background: #DBEAFE;
    color: #2563EB;
    font-size: 13px;
    font-weight: 500;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.active { background: #D1FAE5; color: #10B981; }
.status-badge.inactive { background: #FEE2E2; color: #EF4444; }
.status-badge.pending { background: #FEF3C7; color: #F59E0B; }
.status-badge.suspended { background: #FEE2E2; color: #DC2626; }

@media (max-width: 768px) {
    .profile-stats {
        flex-direction: column;
        gap: 8px;
    }
    
    .stat-divider {
        display: none;
    }
}
</style>

<script>
$(document).ready(function() {
    // Activity Chart
    <?php if (!empty($chart_data)): ?>
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_data['labels'] ?? []); ?>,
            datasets: [{
                label: 'Activities',
                data: <?php echo json_encode($chart_data['data'] ?? []); ?>,
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2563EB'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F5F9'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>
});

// Helper function for time ago
function time_ago($timestamp) {
    // This would be implemented in PHP or JavaScript
    return 'Just now';
}
</script>