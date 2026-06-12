<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * Shared behaviour for the system's data-dictionary enums.
 *
 * Labels are never stored on the enum itself: they live in the
 * lang/{locale}/enums.php files so the same enum drives both the French
 * and English interfaces. The frontend consumes options() through the
 * enum endpoint, which keeps option lists out of the React code.
 */
trait HasOptions
{
    /** Translation key for the current case, e.g. "enums.type_equipement.PC". */
    public function translationKey(): string
    {
        return 'enums.'.Str::snake(class_basename(self::class)).'.'.$this->value;
    }

    /** Human label for the current locale (falls back to the raw value). */
    public function label(): string
    {
        $key = $this->translationKey();
        $translated = __($key);

        return $translated === $key ? $this->value : $translated;
    }

    /** The raw backing values, e.g. for validation rules. */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /** Value/label pairs for the API, shaping the frontend's select inputs. */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
