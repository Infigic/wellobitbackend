@extends('layouts.app')

<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

@section('content')
    <body>
        <div class="dashboard">
            <div class="header">
                <h1>Marketing Dashboard</h1>
                <p class="subtitle">Acquisition → Activation → Engagement → Retention → Monetization</p>
            </div>

            <!-- Top Metrics -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Users</div>
                    <div class="metric-value blue">{{ $totals['total_users_trackings'] }}</div>
                    <div class="metric-change">+ {{ $totals['users_this_week'] }} this week</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label"></div>
                    <div class="metric-value green">{{ $totals['activation_rate'] }}%</div>
                    <div class="metric-change">First session completed</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Active Today</div>
                    <div class="metric-value purple">{{ $totals['active_today'] }}</div>
                    <div class="metric-change">
                        {{ $totals['active_today'] > 0 ? round(($totals['active_today'] / $totals['total_users_trackings']) * 100, 2) : 0 }}%
                        of total</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Trial Users</div>
                    <div class="metric-value orange">{{ $totals['total_trial_users'] }}</div>
                    {{-- <div class="metric-change">7.1% on trial</div> --}}
                </div>
                <?php
                $conversionRate = $totals['total_users_trackings'] > 0 ? round(($totals['trial_to_paid_conversion_rate'] / $totals['total_users_trackings']) * 100, 2) : 0;
                ?>
                <div class="metric-card">
                    <div class="metric-label">Trial → Paid</div>
                    <div class="metric-value green">{{ $conversionRate }}%</div>
                    <div class="metric-change">Conversion rate</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Paid Users</div>
                    <div class="metric-value green">{{ $totals['total_paid_users'] }}</div>
                    <div class="metric-change">{{ $conversionRate }} paying</div>
                </div>
            </div>

            <!-- Marketing Action Cards -->
            <div class="marketing-actions">
                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">📱</div>
                        <div class="action-title">New This Week</div>
                    </div>
                    <div class="action-stat green">{{ $totals['users_this_week'] }}</div>
                    <div class="action-description">Fresh installs in last 7 days. Send welcome sequence.</div>
                    <button class="action-button btn-success" onclick="exportSegment('new-this-week')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">❌</div>
                        <div class="action-title">No First Session</div>
                    </div>
                    <div class="action-stat red">{{ $totals['no_first_session_users'] }}</div>
                    <div class="action-description">Installed but never started. Critical activation campaign.</div>
                    <button class="action-button btn-danger" onclick="exportSegment('no-first-session')">Export
                        List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">⏰</div>
                        <div class="action-title">Trial Ending Soon</div>
                    </div>
                    <div class="action-stat orange">{{ $totals['trial_ending_soon'] }}</div>
                    <div class="action-description">Trial expires in 3 days. Conversion opportunity.</div>
                    <button class="action-button btn-warning" onclick="exportSegment('trial-ending')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">💎</div>
                        <div class="action-title">Trial Expired</div>
                    </div>
                    <div class="action-stat blue">{{ $totals['expired_trial_users'] }}</div>
                    <div class="action-description">Trial ended, still active. Win-back offer time.</div>
                    <button class="action-button btn-info" onclick="exportSegment('trial-expired')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">💰</div>
                        <div class="action-title">Paid Users</div>
                    </div>
                    <div class="action-stat green">{{ $totals['total_paid_users'] }}</div>
                    <div class="action-description">Active subscribers. VIP features and retention.</div>
                    <button class="action-button btn-success" onclick="exportSegment('paid-users')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">⚠️</div>
                        <div class="action-title">At Risk (7d)</div>
                    </div>
                    <div class="action-stat orange">{{ $totals['inactive_7_days'] }}</div>
                    <div class="action-description">Inactive 7+ days. Re-engagement needed.</div>
                    <button class="action-button btn-warning" onclick="exportSegment('at-risk-7d')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">🚨</div>
                        <div class="action-title">At Risk (30d)</div>
                    </div>
                    <div class="action-stat red">{{ $totals['inactive_30_days'] }}</div>
                    <div class="action-description">Inactive 30+ days. Last-chance win-back.</div>
                    <button class="action-button btn-danger" onclick="exportSegment('at-risk-30d')">Export List</button>
                </div>

                <div class="action-card">
                    <div class="action-header">
                        <div class="action-icon">📱</div>
                        <div class="action-title">Incomplete Setup</div>
                    </div>
                    <div class="action-stat purple">{{ $totals['incomplete_setup_users'] }}</div>
                    <div class="action-description">No Apple Watch connected. Setup reminder.</div>
                    <button class="action-button btn-info" onclick="exportSegment('incomplete-setup')">Export List</button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="filters-title">🎯 Filter Users</div>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">Acquisition Channel</label>
                        <select name="acquisition_channel" class="dashboard-filter" onchange="applyFilters()">
                            <option value="">All Channels</option>

                            @foreach ($acquisitionChannels as $value => $label)
                                <option value="{{ $value }}"
                                    {{ request('acquisition_channel') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Subscription Status</label>
                        <select name="subscription_status" class="dashboard-filter" onchange="applyFilters()">
                            <option value="">All Status</option>

                            @foreach ($subscriptionStatuses as $value => $label)
                                <option value="{{ $value }}"
                                    {{ request('subscription_status') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Onboarding Stage</label>

                        <select name="onboarding_stage"
                                class="dashboard-filter"
                                onchange="applyFilters()">
                            <option value="">All Stages</option>

                            @foreach ($onboardingStages as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('onboarding_stage') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Apple Watch</label>
                        <select name="apple_watch" class="dashboard-filter" onchange="applyFilters()">
                            <option value="">All</option>

                            @foreach ($appleWatchStatuses as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('apple_watch') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Activity Status</label>
                        <select name="activity_status" class="dashboard-filter" onchange="applyFilters()">
                            <option value="">All Users</option>

                            @foreach ($activityStatuses as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('activity_status') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Primary Reason</label>
                        <select name="primary_reason" class="dashboard-filter" onchange="applyFilters()">
                            <option value="">All Reasons</option>

                            @foreach ($primaryReasons as $key => $label)
                                <option value="{{ $key }}"
                                    {{ request('primary_reason') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users-section table-responsive">
                <div class="section-header">
                    <div class="section-title">User List</div>
                    <div class="header-actions">
                        <button class="export-btn" onclick="exportAll()">📥 Export to CSV</button>
                    </div>
                </div>
                {{ $dataTable->table() }}
            </div>
        </div>
    @endsection

    <!-- DataTable Scripts -->
    {{ $dataTable->scripts() }}

    <script>
        function exportAll(segment = null) {
            let url = '{{ route('dashboard.exportCSV') }}';
            if (segment) {
                url += '?segment=' + segment;
            }
            window.location.href = url;
        }

        function exportSegment(segment) {
            // Map UI segment names to backend segment names
            const segmentMap = {
                'new-this-week': 'new-this-week',
                'trial-ending': 'trial-ending',
                'trial-expired': 'expired-trial',
                'paid-users': 'paid',
                'at-risk-7d': 'inactive-7',
                'at-risk-30d': 'inactive-30',
                'incomplete-setup': 'incomplete-setup',
                'no-first-session': 'no-first-session'
            };

            const backendSegment = segmentMap[segment] || segment;
            exportAll(backendSegment);
        }
    </script>

    <!-- Filter Scripts -->
    <script>
    function applyFilters() {
        const table = $('#users-table').DataTable();

        table.ajax.reload();
    }
    </script>

</body>

</html>
