@component('mail::message')
# Welcome to {{ $appName }}

Dear {{ $user->name }},

Your account has been created. Please find your login details below.

**Full name:** {{ $user->name }}

**Email:** {{ $user->email }}

**Temporary password:** {{ $plainPassword }}

**Position:** {{ $roleName }}

**Reporting manager:** {{ $managerName ?? '—' }}

**Phone:** {{ $user->phone ?? '—' }}

**Status:** {{ $user->is_active ? 'Active' : 'Inactive' }}

Please log in using the button below and **change your password** after your first login.

@component('mail::button', ['url' => $loginUrl])
Log in to {{ $appName }}
@endcomponent

If you have any questions, please contact your administrator or support.

Thanks,<br>
{{ $appName }}
@endcomponent
