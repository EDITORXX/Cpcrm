<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Lead - CRM Automation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label .required { color: #dc3545; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #205A44; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: #205A44; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section-title { font-size: 18px; font-weight: 600; color: #333; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        .section-title:first-child { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create New Lead</h1>
            <p style="color: #666; margin-top: 5px;">Add a new lead manually and assign to a user</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('crm.automation.leads.store') }}" class="card">
            @csrf

            <h2 class="section-title">Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter lead name">
                </div>

                <div class="form-group">
                    <label>Phone/Number <span class="required">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="Enter phone number">
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address">
            </div>

            <h2 class="section-title">Location Details</h2>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2" placeholder="Enter full address">{{ old('address') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="{{ old('city') }}" placeholder="Enter city">
                </div>

                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="{{ old('state') }}" placeholder="Enter state">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" value="{{ old('pincode') }}" placeholder="Enter pincode">
                </div>

                <div class="form-group">
                    <label>Preferred Location</label>
                    <input type="text" name="preferred_location" value="{{ old('preferred_location') }}" placeholder="e.g., South Mumbai, Bandra">
                </div>
            </div>

            <h2 class="section-title">Property Requirements</h2>

            <div class="form-row">
                <div class="form-group">
                    <label>Preferred Size</label>
                    <input type="text" name="preferred_size" value="{{ old('preferred_size') }}" placeholder="e.g., 2 BHK, 1200 sqft">
                </div>

                <div class="form-group">
                    <label>Property Type</label>
                    <select name="property_type">
                        <option value="">-- Select Type --</option>
                        <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="villa" {{ old('property_type') == 'villa' ? 'selected' : '' }}>Villa</option>
                        <option value="plot" {{ old('property_type') == 'plot' ? 'selected' : '' }}>Plot</option>
                        <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="other" {{ old('property_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Budget Min</label>
                    <input type="number" name="budget_min" value="{{ old('budget_min') }}" min="0" step="0.01" placeholder="Minimum budget">
                </div>

                <div class="form-group">
                    <label>Budget Max</label>
                    <input type="number" name="budget_max" value="{{ old('budget_max') }}" min="0" step="0.01" placeholder="Maximum budget">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Investment</label>
                    <input type="number" name="investment" value="{{ old('investment') }}" min="0" step="0.01" placeholder="Investment amount">
                </div>

                <div class="form-group">
                    <label>Source</label>
                    <select name="source">
                        <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Other</option>
                        <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>Website</option>
                        <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="walk_in" {{ old('source') == 'walk_in' ? 'selected' : '' }}>Walk In</option>
                        <option value="call" {{ old('source') == 'call' ? 'selected' : '' }}>Call</option>
                        <option value="social_media" {{ old('source') == 'social_media' ? 'selected' : '' }}>Social Media</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Use/End Use</label>
                <textarea name="use_end_use" rows="3" placeholder="e.g., Residential, Investment, Commercial use">{{ old('use_end_use') }}</textarea>
            </div>

            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements" rows="3" placeholder="Additional requirements or preferences">{{ old('requirements') }}</textarea>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Any additional notes">{{ old('notes') }}</textarea>
            </div>

            <h2 class="section-title">Assignment</h2>

            <div class="form-group">
                <label>Assign To User (Optional)</label>
                <select name="assigned_to">
                    <option value="">-- Don't Assign Now --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role->name }})
                        </option>
                    @endforeach
                </select>
                <p style="color: #666; font-size: 12px; margin-top: 5px;">You can assign this lead to a user now or assign later</p>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="create_calling_task" value="1" 
                           {{ old('create_calling_task', '1') == '1' ? 'checked' : '' }} 
                           style="width: auto; margin-right: 8px; cursor: pointer;">
                    <span>Create calling task for assigned user</span>
                </label>
                <p style="color: #666; font-size: 12px; margin-top: 5px; margin-left: 28px;">
                    If checked, a calling task will be automatically created for the assigned user when lead is created
                </p>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Create Lead</button>
                <a href="{{ route('crm.automation.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

