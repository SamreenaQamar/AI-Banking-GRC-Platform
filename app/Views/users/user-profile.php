<?php
/**
 * User Profile Page
 * 
 * @var string $title
 * @var object $profile_user
 * @var array $stats
 * @var array $activities
 */
?>

<?php $page_title = 'User Profile'; ?>
<?php $active_page = 'users'; ?>

<div class="user-profile-container">
    <div class="row g-4">
        <!-- Profile Sidebar -->
        <div class="col-xl-3 col-lg-4">
            <div class="card profile-card">
                <div class="profile-cover">
                    <div class="profile-avatar-wrapper">
                        <?php if ($profile_user->profile_image): ?>
                            <img src="<?php echo UPLOADS_URL; ?>/<?php echo $profile_user->profile_image; ?>" 
                                 alt="Profile" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar placeholder">
                                <span><?php echo strtoupper(substr($profile_user->first_name ?? 'U', 0, 1) . substr($profile_user->last_name ?? '', 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h5 class="profile-name"><?php echo htmlspecialchars($profile_user->full_name ?? $profile_user->username); ?></h5>
                    <p class="profile-role">
                        <span class="role-badge"><?php echo htmlspecialchars($profile_user->role_display_name ?? 'User'); ?></span>
                    </p>
                    <p class="profile-email">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($profile_user->email); ?>
                    </p>
                    
                    <div class="profile-stats mt-3">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['total_activities'] ?? 0; ?></span>
                            <span class="stat-label">Activities</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['compliance_tasks'] ?? 0; ?></span>
                            <span class="stat-label">Compliance</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['risks_managed'] ?? 0; ?></span>
                            <span class="stat-label">Risks</span>
                        </div>
                    </div>
                    
                    <div class="profile-meta mt-3">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Joined: <?php echo date('d M Y', strtotime($profile_user->created_at)); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Last Login: <?php echo $profile_user->last_login ? date('d M Y h:i A', strtotime($profile_user->last_login)) : 'Never'; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span>Department: <?php echo htmlspecialchars($profile_user->department_name ?? 'N/A'); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-id-badge"></i>
                            <span>Employee ID: <?php echo htmlspecialchars($profile_user->employee_id ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($current_user->id ?? 0 === $profile_user->id || $current_user->role_id <= 2): ?>
                        <div class="profile-actions mt-3">
                            <a href="<?php echo BASE_URL; ?>/users/<?php echo $profile_user->id; ?>/edit" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Activity Overview -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Activity Overview
                    <span class="badge bg-primary ms-2">Last 30 Days</span>
                </div>
                <div class="card-body">
                    <canvas id="userActivityChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i> Recent Activities</span>
                    <span class="text-muted small"><?php echo count($activities ?? []); ?> activities</span>
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
                                                <?php echo date('d M Y h:i A', strtotime($activity->created_at)); ?>
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
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- User Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> User Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Full Name</label>
                                <div class="info-value"><?php echo htmlspecialchars($profile_user->full_name ?? $profile_user->username); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Username</label>
                                <div class="info-value">@<?php echo htmlspecialchars($profile_user->username); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Email</label>
                                <div class="info-value"><?php echo htmlspecialchars($profile_user->email); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Phone</label>
                                <div class="info-value"><?php echo htmlspecialchars($profile_user->phone ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Department</label>
                                <div class="info-value"><?php echo htmlspecialchars($profile_user->department_name ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Role</label>
                                <div class="info-value">
                                    <span class="role-badge"><?php echo htmlspecialchars($profile_user->role_display_name ?? 'User'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Status</label>
                                <div class="info-value">
                                    <span class="status-badge <?php echo $profile_user->status; ?>">
                                        <?php echo ucfirst($profile_user->status); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">2FA Status</label>
                                <div class="info-value">
                                    <?php if ($profile_user->two_factor_enabled ?? false): ?>
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

<style>
.user-profile-container {
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

.profile-role {
    margin-bottom: 4px;
}

.role-badge {
    padding: 4px 16px;
    border-radius: 12px;
    background: #DBEAFE;
    color: #2563EB;
    font-size: 13px;
    font-weight: 500;
}

.profile-email {
    color: #64748B;
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

.profile-meta {
    margin-top: 12px;
    text-align: left;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
    font-size: 13px;
    color: #64748B;
}

.meta-item i {
    width: 16px;
    color: #94A3B8;
}

.profile-actions {
    padding-top: 12px;
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

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.active { background: #D1FAE5; color: #10B981; }
.status-badge.inactive { background: #FEE2E2; color: #EF4444; }
.status-badge.suspended { background: #FEF3C7; color: #F59E0B; }
.status-badge.pending { background: #F1F5F9; color: #64748B; }

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
    // User Activity Chart
    <?php if (isset($chart_data)): ?>
    const ctx = document.getElementById('userActivityChart').getContext('2d');
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
</script>