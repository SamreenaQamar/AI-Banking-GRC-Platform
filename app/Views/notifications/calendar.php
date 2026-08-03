<?php
/**
 * Notification Calendar Page
 * 
 * @var string $title
 * @var array $calendar_data
 * @var array $events
 */
?>

<?php $page_title = 'Notification Calendar'; ?>
<?php $active_page = 'notifications'; ?>

<div class="calendar-container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h5><i class="fas fa-calendar-alt me-2 text-primary"></i> Notification Calendar</h5>
            <p class="text-muted">View notifications and events by date</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button class="btn btn-outline-primary" id="prevMonth">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-primary" id="currentMonth"><?php echo date('F Y'); ?></button>
                <button class="btn btn-outline-primary" id="nextMonth">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Calendar -->
    <div class="card">
        <div class="card-body p-4">
            <div class="calendar-grid">
                <!-- Day Headers -->
                <div class="calendar-header">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                </div>
                
                <!-- Calendar Days -->
                <div class="calendar-days" id="calendarDays">
                    <?php
                    $firstDay = date('w', strtotime(date('Y-m-01')));
                    $daysInMonth = date('t');
                    $today = date('Y-m-d');
                    
                    // Empty days
                    for ($i = 0; $i < $firstDay; $i++): ?>
                        <div class="calendar-day empty"></div>
                    <?php endfor; ?>
                    
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): 
                        $date = date('Y-m-' . str_pad($day, 2, '0', STR_PAD_LEFT));
                        $isToday = $date === $today;
                        $hasEvents = isset($calendar_data[$date]) && !empty($calendar_data[$date]);
                    ?>
                        <div class="calendar-day <?php echo $isToday ? 'today' : ''; ?> <?php echo $hasEvents ? 'has-events' : ''; ?>" 
                             data-date="<?php echo $date; ?>">
                            <span class="day-number"><?php echo $day; ?></span>
                            <?php if ($hasEvents): ?>
                                <span class="event-dot"></span>
                            <?php endif; ?>
                            <?php if ($isToday): ?>
                                <span class="today-badge">Today</span>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Events List -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-list me-2"></i> <span id="selectedDateLabel">Today's Events</span>
        </div>
        <div class="card-body p-0" id="eventsList">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-item">
                        <div class="event-time">
                            <?php if ($event->time): ?>
                                <?php echo date('h:i A', strtotime($event->time)); ?>
                            <?php else: ?>
                                All Day
                            <?php endif; ?>
                        </div>
                        <div class="event-dot <?php echo $event->type; ?>"></div>
                        <div class="event-content">
                            <div class="event-title"><?php echo htmlspecialchars($event->title); ?></div>
                            <div class="event-description"><?php echo htmlspecialchars($event->description); ?></div>
                        </div>
                        <div class="event-status">
                            <span class="badge bg-<?php echo $event->status === 'completed' ? 'success' : ($event->status === 'pending' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst($event->status ?? 'Pending'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar fa-2x mb-2"></i>
                    <p>No events for this day</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.calendar-container {
    padding: 0;
}

.calendar-grid {
    user-select: none;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}

.calendar-day-header {
    text-align: center;
    font-weight: 600;
    font-size: 13px;
    color: #64748B;
    padding: 8px 0;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.calendar-day {
    aspect-ratio: 1;
    border-radius: 8px;
    background: #F8FAFC;
    padding: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    min-height: 60px;
}

.calendar-day:hover {
    background: #F1F5F9;
}

.calendar-day.empty {
    background: transparent;
    cursor: default;
}

.calendar-day.today {
    background: #DBEAFE;
    border: 2px solid #2563EB;
}

.calendar-day.has-events {
    background: #F0F7FF;
}

.calendar-day .day-number {
    font-size: 14px;
    font-weight: 500;
    color: #1E293B;
}

.calendar-day.today .day-number {
    color: #2563EB;
    font-weight: 700;
}

.calendar-day .event-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #2563EB;
    margin-top: 2px;
}

.calendar-day .today-badge {
    position: absolute;
    bottom: 4px;
    font-size: 8px;
    color: #2563EB;
    font-weight: 600;
}

.event-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 20px;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.2s;
}

.event-item:hover {
    background: #F8FAFC;
}

.event-item:last-child {
    border-bottom: none;
}

.event-time {
    font-size: 13px;
    color: #64748B;
    min-width: 80px;
    font-weight: 500;
}

.event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.event-dot.compliance { background: #3B82F6; }
.event-dot.risk { background: #F59E0B; }
.event-dot.audit { background: #10B981; }
.event-dot.policy { background: #EC4899; }
.event-dot.system { background: #64748B; }

.event-content {
    flex: 1;
}

.event-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.event-description {
    font-size: 13px;
    color: #64748B;
}

.event-status {
    margin-left: 12px;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 40px;
        padding: 4px;
    }
    
    .calendar-day .day-number {
        font-size: 12px;
    }
    
    .event-item {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .event-time {
        min-width: auto;
        font-size: 12px;
    }
}
</style>

<script>
$(document).ready(function() {
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    
    // Month navigation
    $('#prevMonth').on('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        loadCalendar(currentMonth, currentYear);
    });
    
    $('#nextMonth').on('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        loadCalendar(currentMonth, currentYear);
    });
    
    function loadCalendar(month, year) {
        // This would be an AJAX call to load calendar data
        // For now, just update the month display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        $('#currentMonth').text(monthNames[month] + ' ' + year);
    }
    
    // Day click
    $('.calendar-day:not(.empty)').on('click', function() {
        const date = $(this).data('date');
        if (date) {
            $('#selectedDateLabel').text(new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
            // Load events for this date
        }
    });
});
</script>