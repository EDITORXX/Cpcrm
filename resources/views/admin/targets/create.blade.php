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
                <select name="user_id" id="user_id" required onchange="toggleClosersField()">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" 
                                data-role="{{ $user->role->slug }}" 
                                {{ (old('user_id') ?? $userId) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }}) - {{ $user->getDisplayRoleName() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Target Month <span class="required">*</span></label>
                <input type="month" name="month" value="{{ old('month', $month) }}" required>
            </div>

            <h2 class="section-title">User Targets</h2>

            <!-- Prospect Targets (Hidden for Sales Managers) -->
            <div id="prospect-targets-section">
                <div class="form-row">
                    <div class="form-group">
                        <label>Prospects to Extract</label>
                        <input type="number" name="target_prospects_extract" id="target_prospects_extract" value="{{ old('target_prospects_extract', $existingTarget->target_prospects_extract ?? 0) }}" min="0" placeholder="0">
                        <small style="color: #666;">Number of prospects the user should extract/create</small>
                    </div>

                    <div class="form-group">
                        <label>Prospects to Verify</label>
                        <input type="number" name="target_prospects_verified" id="target_prospects_verified" value="{{ old('target_prospects_verified', $existingTarget->target_prospects_verified ?? 0) }}" min="0" placeholder="0">
                        <small style="color: #666;">Number of prospects that should be verified/approved</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Calls to Make</label>
                    <input type="number" name="target_calls" id="target_calls" value="{{ old('target_calls', $existingTarget->target_calls ?? 0) }}" min="0" placeholder="0">
                    <small style="color: #666;">Number of phone calls the user should complete</small>
                </div>
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

            <div class="form-group" id="closers-field" style="display: none;">
                <label>Closers</label>
                <input type="number" name="target_closers" value="{{ old('target_closers', $existingTarget->target_closers ?? 0) }}" min="0" placeholder="0">
                <small style="color: #666;">Only for Sales Managers and Sales Executives</small>
            </div>

            <!-- Incentive Rates Section -->
            <h2 class="section-title">Incentive Rates (Optional)</h2>
            
            <div class="form-group" id="incentive-per-closer-field" style="display: none;">
                <label>Incentive per Closer (₹)</label>
                <input type="number" name="incentive_per_closer" id="incentive_per_closer" step="0.01" min="0" value="{{ old('incentive_per_closer', $existingTarget->incentive_per_closer ?? '') }}" placeholder="0.00">
                <small style="color: #666;">Incentive amount per closer for Managers and Sales Executives</small>
            </div>

            <div class="form-group" id="incentive-per-visit-field" style="display: none;">
                <label>Incentive per Visit (₹)</label>
                <input type="number" name="incentive_per_visit" id="incentive_per_visit" step="0.01" min="0" value="{{ old('incentive_per_visit', $existingTarget->incentive_per_visit ?? '') }}" placeholder="0.00">
                <small style="color: #666;">Incentive amount per site visit for Sales Executives</small>
            </div>

            <!-- Manager Target Calculation Logic (Only for Sales Managers) -->
            <div id="manager-logic-section" style="display: none;">
                <h2 class="section-title">Manager Target Calculation Logic</h2>
                
                <div class="form-group">
                    <label>Calculation Logic <span class="required">*</span></label>
                    <select name="manager_target_calculation_logic" id="manager_target_calculation_logic" required>
                        <option value="">-- Select Logic --</option>
                        <option value="juniors_sum" {{ old('manager_target_calculation_logic', $existingTarget->manager_target_calculation_logic ?? '') == 'juniors_sum' ? 'selected' : '' }}>
                            Sum of Juniors' Targets (Logic 1)
                        </option>
                        <option value="individual_plus_team" {{ old('manager_target_calculation_logic', $existingTarget->manager_target_calculation_logic ?? '') == 'individual_plus_team' ? 'selected' : '' }}>
                            Individual Target + Team Consolidated (Logic 2)
                        </option>
                    </select>
                    <small style="color: #666;">
                        <strong>Logic 1:</strong> Manager's target = Sum of all juniors' targets<br>
                        <strong>Logic 2:</strong> Manager's target = Individual target + Sum of juniors' targets
                    </small>
                </div>

                <div class="form-group">
                    <label>Junior Scope <span class="required">*</span></label>
                    <select name="manager_junior_scope" id="manager_junior_scope" required>
                        <option value="">-- Select Scope --</option>
                        <option value="executives_only" {{ old('manager_junior_scope', $existingTarget->manager_junior_scope ?? '') == 'executives_only' ? 'selected' : '' }}>
                            Executives Only
                        </option>
                        <option value="executives_and_telecallers" {{ old('manager_junior_scope', $existingTarget->manager_junior_scope ?? '') == 'executives_and_telecallers' ? 'selected' : '' }}>
                            Executives + Sales Executives
                        </option>
                    </select>
                    <small style="color: #666;">Select which juniors to include in target calculation</small>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Set Target</button>
                <a href="{{ route('admin.targets.index', ['month' => $month]) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        function toggleClosersField() {
            const userSelect = document.getElementById('user_id');
            const closersField = document.getElementById('closers-field');
            const managerLogicSection = document.getElementById('manager-logic-section');
            const prospectTargetsSection = document.getElementById('prospect-targets-section');
            const incentivePerCloserField = document.getElementById('incentive-per-closer-field');
            const incentivePerVisitField = document.getElementById('incentive-per-visit-field');
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const role = selectedOption.getAttribute('data-role');
                
                // Hide prospect targets for Sales Managers
                if (role === 'sales_manager') {
                    prospectTargetsSection.style.display = 'none';
                    // Set prospect fields to 0 for managers
                    document.getElementById('target_prospects_extract').value = 0;
                    document.getElementById('target_prospects_verified').value = 0;
                    document.getElementById('target_calls').value = 0;
                } else {
                    prospectTargetsSection.style.display = 'block';
                }
                
                // Show closers field for Sales Managers and Sales Executives
                if (role === 'sales_manager' || role === 'sales_executive') {
                    closersField.style.display = 'block';
                } else {
                    closersField.style.display = 'none';
                }
                
                // Show incentive per closer for Sales Managers and Sales Executives
                if (role === 'sales_manager' || role === 'sales_executive') {
                    incentivePerCloserField.style.display = 'block';
                } else {
                    incentivePerCloserField.style.display = 'none';
                }
                
                // Show incentive per visit for Telecallers
                if (role === 'telecaller') {
                    incentivePerVisitField.style.display = 'block';
                } else {
                    incentivePerVisitField.style.display = 'none';
                }
                
                // Show manager logic section only for Sales Managers
                if (role === 'sales_manager') {
                    managerLogicSection.style.display = 'block';
                    // Make fields required
                    document.getElementById('manager_target_calculation_logic').required = true;
                    document.getElementById('manager_junior_scope').required = true;
                } else {
                    managerLogicSection.style.display = 'none';
                    // Make fields not required
                    document.getElementById('manager_target_calculation_logic').required = false;
                    document.getElementById('manager_junior_scope').required = false;
                }
            } else {
                prospectTargetsSection.style.display = 'block';
                closersField.style.display = 'none';
                managerLogicSection.style.display = 'none';
                incentivePerCloserField.style.display = 'none';
                incentivePerVisitField.style.display = 'none';
                document.getElementById('manager_target_calculation_logic').required = false;
                document.getElementById('manager_junior_scope').required = false;
            }
        }
        
        // Call on page load if user is already selected
        document.addEventListener('DOMContentLoaded', function() {
            toggleClosersField();
        });
    </script>
</body>
</html>

