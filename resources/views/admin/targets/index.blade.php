<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Management - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-primary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-secondary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-secondary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 8px 16px; font-size: 14px; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .filter-bar { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; }
        .progress-bar { width: 100%; height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: #205A44; transition: width 0.3s; }
        .progress-fill.warning { background: #ffc107; }
        .progress-fill.danger { background: #dc3545; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Target Management</h1>
                <p style="color: #666; margin-top: 5px;">
                    @if(auth()->user()->isSalesHead())
                        Set targets for Sales Executives and Sales Managers. Telecaller targets are view-only.
                    @else
                        Set and manage monthly targets for Telecallers, Sales Executives, and Sales Managers
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.targets.create') }}" class="btn btn-primary">+ Set New Target</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.targets.index') }}" style="display: flex; gap: 15px; align-items: center;">
                <label>Month:</label>
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>

        <div class="card">
            @if($targets->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Month</th>
                            <th>Prospects Extract</th>
                            <th>Prospects Verified</th>
                            <th>Calls</th>
                            <th>Visits</th>
                            <th>Meetings</th>
                            <th>Closers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                            @php
                                $progress = $target->getProgressData();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $target->user->name }}</strong><br>
                                    <small style="color: #666;">{{ $target->user->email }}</small><br>
                                    <span class="badge {{ $target->user->isSalesExecutive() ? 'badge-info' : ($target->user->isSalesManager() ? 'badge-warning' : 'badge-success') }}" style="margin-top: 4px; display: inline-block;">
                                        {{ $target->user->getDisplayRoleName() }}
                                    </span>
                                    @if($target->user->isSalesManager() && $target->manager_target_calculation_logic)
                                        <br>
                                        <small style="color: #16a34a; font-weight: 600; margin-top: 4px; display: inline-block;">
                                            @if($target->manager_target_calculation_logic === 'juniors_sum')
                                                Logic 1: Juniors Sum
                                            @else
                                                Logic 2: Individual + Team
                                            @endif
                                            @if($target->manager_junior_scope)
                                                ({{ $target->manager_junior_scope === 'executives_only' ? 'Executives Only' : 'Executives + Telecallers' }})
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td><strong>{{ $target->target_month->format('M Y') }}</strong></td>
                                <td>
                                    @if($target->user->isSalesManager())
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['prospects_extract']['actual'] }} / {{ $target->target_prospects_extract }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['prospects_extract']['percentage'] >= 100 ? '' : ($progress['prospects_extract']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['prospects_extract']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['prospects_extract']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if($target->user->isSalesManager())
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['prospects_verified']['actual'] }} / {{ $target->target_prospects_verified }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['prospects_verified']['percentage'] >= 100 ? '' : ($progress['prospects_verified']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['prospects_verified']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['prospects_verified']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if($target->user->isSalesManager())
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['calls']['actual'] }} / {{ $target->target_calls }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['calls']['percentage'] >= 100 ? '' : ($progress['calls']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['calls']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['calls']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $visitsTarget = $target->user->isSalesManager() && $target->manager_target_calculation_logic 
                                            ? $target->calculateManagerTarget('visits') 
                                            : ($target->target_visits ?? 0);
                                    @endphp
                                    @if($visitsTarget > 0)
                                        <div>{{ $progress['visits']['achieved'] }} / {{ $visitsTarget }}</div>
                                        @php
                                            $visitsPercentage = $visitsTarget > 0 ? min(100, round(($progress['visits']['achieved'] / $visitsTarget) * 100, 1)) : 0;
                                        @endphp
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $visitsPercentage >= 100 ? '' : ($visitsPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $visitsPercentage }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($visitsPercentage, 1) }}%</small>
                                        @if($target->user->isSalesManager() && $target->manager_target_calculation_logic && $visitsTarget != $target->target_visits)
                                            <br><small style="color: #16a34a; font-size: 10px;">(Calculated)</small>
                                        @endif
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $meetingsTarget = $target->user->isSalesManager() && $target->manager_target_calculation_logic 
                                            ? $target->calculateManagerTarget('meetings') 
                                            : ($target->target_meetings ?? 0);
                                    @endphp
                                    @if($meetingsTarget > 0)
                                        <div>{{ $progress['meetings']['achieved'] }} / {{ $meetingsTarget }}</div>
                                        @php
                                            $meetingsPercentage = $meetingsTarget > 0 ? min(100, round(($progress['meetings']['achieved'] / $meetingsTarget) * 100, 1)) : 0;
                                        @endphp
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $meetingsPercentage >= 100 ? '' : ($meetingsPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $meetingsPercentage }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($meetingsPercentage, 1) }}%</small>
                                        @if($target->user->isSalesManager() && $target->manager_target_calculation_logic && $meetingsTarget != $target->target_meetings)
                                            <br><small style="color: #16a34a; font-size: 10px;">(Calculated)</small>
                                        @endif
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($target->user->isSalesManager() || $target->user->isSalesExecutive())
                                        @if($target->target_closers > 0)
                                            <div>{{ $progress['closers']['achieved'] }} / {{ $target->target_closers }}</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill {{ $progress['closers']['percentage'] >= 100 ? '' : ($progress['closers']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ min(100, $progress['closers']['percentage']) }}%"></div>
                                            </div>
                                            <small style="color: #666;">{{ number_format($progress['closers']['percentage'], 1) }}%</small>
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    @else
                                        <span style="color: #999;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->isSalesHead() && $target->user->isTelecaller())
                                        <span style="color: #6b7280; font-size: 14px;">
                                            <i class="fas fa-eye mr-2"></i>View Only
                                        </span>
                                    @else
                                        <a href="{{ route('admin.targets.edit', $target->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.targets.destroy', $target->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this target?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="text-align: center; color: #666; padding: 40px;">No targets found for this month. <a href="{{ route('admin.targets.create', ['month' => $month]) }}">Create one now</a></p>
            @endif
        </div>
    </div>
</body>
</html>

