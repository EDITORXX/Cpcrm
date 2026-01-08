<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Target - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label .required { color: #dc3545; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #205A44; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-primary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-secondary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-secondary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section-title { font-size: 18px; font-weight: 600; color: #333; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        .section-title:first-child { margin-top: 0; }
        .info-box { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #2196f3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Set Monthly Target</h1>
            <p style="color: #666; margin-top: 5px;">Set targets for Sales Executive or Sales Manager for a specific month</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($existingTarget)
            <div class="info-box">
                <strong>Note:</strong> A target already exists for this user and month. Updating will replace the existing target.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.targets.store') }}" class="card">
            @csrf

            <div class="form-group">
                <label>Select User <span class="required">*</span></label>
                <select name="user_id" required>
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id') ?? $userId) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }}) - {{ $user->role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Target Month <span class="required">*</span></label>
                <input type="month" name="month" value="{{ old('month', $month) }}" required>
            </div>

            <h2 class="section-title">User Targets</h2>

            <div class="form-row">
                <div class="form-group">
                    <label>Prospects to Extract</label>
                    <input type="number" name="target_prospects_extract" value="{{ old('target_prospects_extract', $existingTarget->target_prospects_extract ?? 0) }}" min="0" placeholder="0">
                    <small style="color: #666;">Number of prospects the user should extract/create</small>
                </div>

                <div class="form-group">
                    <label>Prospects to Verify</label>
                    <input type="number" name="target_prospects_verified" value="{{ old('target_prospects_verified', $existingTarget->target_prospects_verified ?? 0) }}" min="0" placeholder="0">
                    <small style="color: #666;">Number of prospects that should be verified/approved</small>
                </div>
            </div>

            <div class="form-group">
                <label>Calls to Make</label>
                <input type="number" name="target_calls" value="{{ old('target_calls', $existingTarget->target_calls ?? 0) }}" min="0" placeholder="0">
                <small style="color: #666;">Number of phone calls the user should complete</small>
            </div>

            <h2 class="section-title">Additional Targets (Optional)</h2>

            <div class="form-row">
                <div class="form-group">
                    <label>Site Visits</label>
                    <input type="number" name="target_visits" value="{{ old('target_visits', $existingTarget->target_visits ?? 0) }}" min="0" placeholder="0">
                </div>

                <div class="form-group">
                    <label>Meetings</label>
                    <input type="number" name="target_meetings" value="{{ old('target_meetings', $existingTarget->target_meetings ?? 0) }}" min="0" placeholder="0">
                </div>
            </div>

            <div class="form-group">
                <label>Closers</label>
                <input type="number" name="target_closers" value="{{ old('target_closers', $existingTarget->target_closers ?? 0) }}" min="0" placeholder="0">
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Set Target</button>
                <a href="{{ route('admin.targets.index', ['month' => $month]) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

