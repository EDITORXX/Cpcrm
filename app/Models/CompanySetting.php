<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ColorTemplateService;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'category',
        'group',
        'display_label',
        'display_order',
        'is_required',
        'validation_rules',
        'help_text',
        'updated_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'validation_rules' => 'array',
        'display_order' => 'integer',
    ];

    /**
     * Get the user who last updated this setting.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by group.
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Get value attribute with auto-casting based on type.
     */
    public function getValueAttribute($value)
    {
        // Get raw value from attributes if not passed
        if ($value === null) {
            $value = $this->attributes['setting_value'] ?? null;
        }
        
        if ($value === null || $value === '') {
            return $value;
        }

        $settingType = $this->attributes['setting_type'] ?? $this->setting_type ?? 'string';
        
        switch ($settingType) {
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'text':
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Set value attribute with auto-casting before save.
     */
    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['setting_value'] = null;
            return;
        }

        switch ($this->setting_type) {
            case 'number':
                $this->attributes['setting_value'] = is_numeric($value) ? $value : $value;
                break;
            case 'boolean':
                $this->attributes['setting_value'] = $value ? '1' : '0';
                break;
            case 'json':
                $this->attributes['setting_value'] = is_string($value) ? $value : json_encode($value);
                break;
            default:
                $this->attributes['setting_value'] = (string) $value;
        }
    }

    /**
     * Get setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, $value, ?int $userId = null): self
    {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) {
            // Create new setting with default structure
            $setting = new self();
            $setting->setting_key = $key;
            
            // Determine category and type based on key
            if (str_contains($key, 'color') || str_contains($key, 'gradient') || $key === 'color_template' || $key === 'use_gradient') {
                $setting->category = 'branding';
                $setting->group = 'colors';
                $setting->setting_type = $key === 'use_gradient' ? 'boolean' : 'string';
            } else {
                $setting->category = 'company_profile';
                $setting->group = 'general';
                $setting->setting_type = 'string';
            }
        }
        
        $setting->setting_value = $value;
        if ($userId) {
            $setting->updated_by = $userId;
        }
        $setting->save();
        return $setting;
    }

    /**
     * Get all settings in category as key-value array.
     */
    public static function getAllByCategory(string $category): array
    {
        return self::byCategory($category)
            ->orderBy('display_order')
            ->get()
            ->pluck('value', 'setting_key')
            ->toArray();
    }

    /**
     * Get settings grouped by group.
     */
    public static function getGroupedByCategory(string $category): array
    {
        $settings = self::byCategory($category)
            ->orderBy('display_order')
            ->get();

        $grouped = [];
        foreach ($settings as $setting) {
            $group = $setting->group ?? 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][$setting->setting_key] = $setting->value;
        }

        return $grouped;
    }

    /**
     * Get gradient CSS for the active color template.
     */
    public function getGradientCssAttribute(): string
    {
        $template = self::get('color_template', 'royal_green');
        $useGradient = self::get('use_gradient', true);
        
        if (!$useGradient) {
            $primaryColor = self::get('primary_color', '#205A44');
            return "background-color: {$primaryColor};";
        }

        $gradientStart = self::get('gradient_start');
        $gradientEnd = self::get('gradient_end');
        
        if ($gradientStart && $gradientEnd) {
            return "background: linear-gradient(135deg, {$gradientStart}, {$gradientEnd});";
        }

        // Fallback to template gradient
        return ColorTemplateService::getGradient($template, '135deg') ?? "background: linear-gradient(135deg, #063A1C, #205A44);";
    }

    /**
     * Get active gradient colors.
     */
    public static function getActiveGradient(): array
    {
        $template = self::get('color_template', 'royal_green');
        $useGradient = self::get('use_gradient', true);
        
        if (!$useGradient) {
            return [
                'primary' => self::get('primary_color', '#205A44'),
                'secondary' => self::get('secondary_color', '#063A1C'),
                'accent' => self::get('accent_color', '#15803d'),
            ];
        }

        $gradientStart = self::get('gradient_start');
        $gradientEnd = self::get('gradient_end');
        
        if ($gradientStart && $gradientEnd) {
            return [
                'start' => $gradientStart,
                'end' => $gradientEnd,
                'primary' => self::get('primary_color', '#205A44'),
                'secondary' => self::get('secondary_color', '#063A1C'),
                'accent' => self::get('accent_color', '#15803d'),
            ];
        }

        // Fallback to template
        $templateData = ColorTemplateService::getTemplate($template);
        if ($templateData) {
            return [
                'start' => $templateData['gradient_start'],
                'end' => $templateData['gradient_end'],
                'primary' => $templateData['primary_color'],
                'secondary' => $templateData['secondary_color'],
                'accent' => $templateData['accent_color'],
            ];
        }

        // Ultimate fallback
        return [
            'start' => '#063A1C',
            'end' => '#205A44',
            'primary' => '#205A44',
            'secondary' => '#063A1C',
            'accent' => '#15803d',
        ];
    }
}
