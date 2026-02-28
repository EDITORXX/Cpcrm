@extends('layouts.app')

@section('title', 'Field Mapping - Facebook Lead Ads - Base CRM')
@section('page-title', 'Facebook Lead Ads – Mapping')

@section('header-actions')
    <a href="{{ route('integrations.facebook-lead-ads.forms') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to forms
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Map form fields</h2>
        <p class="text-gray-600 text-sm mb-4">Form: <strong>{{ $fbForm->form_name ?: $fbForm->form_id }}</strong>. Map each Facebook field to a CRM field (or "meta" for extra data).</p>

        <form id="mapping-form">
            @csrf
            <input type="hidden" name="fb_form_id" value="{{ $fbForm->id }}">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 font-medium text-gray-700">Facebook field</th>
                        <th class="text-left py-2 font-medium text-gray-700">CRM field</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fieldNames as $fbName)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 text-gray-800">{{ $fbName }}</td>
                        <td class="py-2">
                            <select name="mapping[{{ $fbName }}]" class="w-full max-w-xs px-3 py-1.5 border border-gray-300 rounded-lg">
                                @foreach($crmKeys as $key)
                                    <option value="{{ $key }}" {{ ($currentMapping[$fbName] ?? '') === $key ? 'selected' : '' }}>{{ $key }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm font-medium">Save & Enable</button>
                <a href="{{ route('integrations.facebook-lead-ads.forms') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('mapping-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    var mapping = {};
    fd.forEach(function(value, key) {
        if (key.startsWith('mapping[')) {
            var name = key.replace('mapping[', '').replace(']', '');
            mapping[name] = value;
        }
    });
    fetch('{{ route("integrations.facebook-lead-ads.save-mapping") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: JSON.stringify({ fb_form_id: form.querySelector('input[name="fb_form_id"]').value, mapping: mapping, _token: document.querySelector('input[name="_token"]').value })
    }).then(r => r.json()).then(function(data) {
        if (data.success) {
            alert(data.message || 'Saved.');
            window.location.href = data.redirect || '{{ route("integrations.facebook-lead-ads.index") }}';
        } else {
            alert(data.message || 'Save failed');
        }
    }).catch(function() { alert('Save failed'); });
});
</script>
@endsection
